<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MetricDataService
{
    /**
     * Get delivery metrics from orders
     */
    public static function getDeliveryMetrics($url): array
    {
        try {
            $ordersCollection = ImportDataService::importOrdersAsCollection(
                $url,
                ['timeout' => 300, 'cache' => 10]
            );

            if ($ordersCollection->isEmpty()) {
                return [
                    'delivered_orders_count' => 0,
                    'total_orders_count' => 0,
                    'delivery_rate_percent' => 0
                ];
            }

            // Filtra pedidos entregues
            $deliveredOrders = $ordersCollection->filter(function ($order) {
                return isset($order['fulfillment_status']) && 
                       $order['fulfillment_status'] === 'Fully Fulfilled';
            });

            $totalOrders = $ordersCollection->count();
            $deliveredCount = $deliveredOrders->count();
            
            // Calcula taxa de entrega
            $deliveryRate = $totalOrders > 0 ? ($deliveredCount / $totalOrders) * 100 : 0;

            return [
                'delivered_orders_count' => $deliveredCount,
                'total_orders_count' => $totalOrders,
                'delivery_rate_percent' => round($deliveryRate, 2)
            ];

        } catch (\Exception $e) {
            Log::error('Error getting delivery metrics: ' . $e->getMessage());
            
            return [
                'delivered_orders_count' => 0,
                'total_orders_count' => 0,
                'delivery_rate_percent' => 0,
                'error' => 'Failed to load metrics'
            ];
        }
    }

      private static function getOrdersCollection(): Collection
    {
        return ImportDataService::importOrdersAsCollection(
            'https://dev-crm.ogruposix.com/candidato-teste-pratico-backend-dashboard/test-orders',
            ['timeout' => 30, 'cache' => 10]
        );
    }

    /**
     * 1. Total de Pedidos
     */
    public static function getTotalOrders(): array
    {
        try {
            $orders = self::getOrdersCollection();
            
            return [
                'total_orders' => $orders->count(),
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error getting total orders: ' . $e->getMessage());
            return ['total_orders' => 0, 'success' => false];
        }
    }

    /**
     * 2. Receita Total
     */
    public static function getTotalRevenue(): array
    {
        try {
            $orders = self::getOrdersCollection();
            
            $totalUSD = $orders->sum(function($order) {
                return isset($order['local_currency_amount']) ? 
                    self::parseCurrency($order['local_currency_amount']) : 0;
            });

            // Conversão USD para BRL (taxa fictícia - ajuste conforme necessidade)
            $exchangeRate = 5.0; // 1 USD = 5 BRL
            $totalBRL = $totalUSD * $exchangeRate;

            return [
                'total_usd' => $totalUSD,
                'total_brl' => $totalBRL,
                'total_usd_formatted' => '$ ' . number_format($totalUSD, 2),
                'total_brl_formatted' => 'R$ ' . number_format($totalBRL, 2),
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error getting total revenue: ' . $e->getMessage());
            return [
                'total_usd' => 0,
                'total_brl' => 0,
                'total_usd_formatted' => '$ 0.00',
                'total_brl_formatted' => 'R$ 0.00',
                'success' => false
            ];
        }
    }

    /**
     * 3. Clientes Únicos
     */
    public static function getUniqueCustomers(): array
    {
        try {
            $orders = self::getOrdersCollection();
            
            $uniqueCustomers = $orders->pluck('customer_id')->unique()->count();
            $totalOrders = $orders->count();
            $avgOrdersPerCustomer = $uniqueCustomers > 0 ? $totalOrders / $uniqueCustomers : 0;

            return [
                'unique_customers' => $uniqueCustomers,
                'total_orders' => $totalOrders,
                'avg_orders_per_customer' => round($avgOrdersPerCustomer, 2),
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error getting unique customers: ' . $e->getMessage());
            return [
                'unique_customers' => 0,
                'total_orders' => 0,
                'avg_orders_per_customer' => 0,
                'success' => false
            ];
        }
    }

    /**
     * 4. Resumo Financeiro (Faturamento x Reembolso x Receita Líquida)
     */
    public static function getFinancialSummary(): array
    {
        try {
            $orders = self::getOrdersCollection();
            
            $grossRevenue = $orders->sum(function($order) {
                return isset($order['local_currency_amount']) ? 
                    self::parseCurrency($order['local_currency_amount']) : 0;
            });

            $totalRefunds = $orders->sum(function($order) {
                $refundAmount = 0;
                if (isset($order['refunds']) && is_array($order['refunds'])) {
                    foreach ($order['refunds'] as $refund) {
                        $refundAmount += isset($refund['total_amount']) ? 
                            self::parseCurrency($refund['total_amount']) : 0;
                    }
                }
                return $refundAmount;
            });

            $netRevenue = $grossRevenue - $totalRefunds;

            return [
                'gross_revenue' => $grossRevenue,
                'total_refunds' => $totalRefunds,
                'net_revenue' => $netRevenue,
                'gross_revenue_formatted' => '$ ' . number_format($grossRevenue, 2),
                'total_refunds_formatted' => '$ ' . number_format($totalRefunds, 2),
                'net_revenue_formatted' => '$ ' . number_format($netRevenue, 2),
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error getting financial summary: ' . $e->getMessage());
            return [
                'gross_revenue' => 0,
                'total_refunds' => 0,
                'net_revenue' => 0,
                'gross_revenue_formatted' => '$ 0.00',
                'total_refunds_formatted' => '$ 0.00',
                'net_revenue_formatted' => '$ 0.00',
                'success' => false
            ];
        }
    }

    /**
     * 5. Taxa de Reembolso
     */
    public static function getRefundRate(): array
    {
        try {
            $orders = self::getOrdersCollection();
            
            $ordersWithRefunds = $orders->filter(function($order) {
                // Verifica se tem refunds ou line_items refunded
                $hasRefunds = !empty($order['refunds']);
                $hasRefundedItems = collect($order['line_items'] ?? [])
                    ->contains('is_refunded', 1);
                
                return $hasRefunds || $hasRefundedItems;
            });

            $totalOrders = $orders->count();
            $refundedOrders = $ordersWithRefunds->count();
            $refundRate = $totalOrders > 0 ? ($refundedOrders / $totalOrders) * 100 : 0;

            // Determina cor do indicador
            $indicatorColor = 'green';
            if ($refundRate > 10) $indicatorColor = 'yellow';
            if ($refundRate > 20) $indicatorColor = 'red';

            return [
                'refund_rate_percent' => round($refundRate, 2),
                'refunded_orders_count' => $refundedOrders,
                'total_orders_count' => $totalOrders,
                'indicator_color' => $indicatorColor,
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error getting refund rate: ' . $e->getMessage());
            return [
                'refund_rate_percent' => 0,
                'refunded_orders_count' => 0,
                'total_orders_count' => 0,
                'indicator_color' => 'green',
                'success' => false
            ];
        }
    }

    /**
     * 6. Produto Mais Vendido
     */
   public static function getBestSellingProduct(): array
{
    try {
        $orders = self::getOrdersCollection();
        
        $products = collect();
        
        foreach ($orders as $order) {
            if (isset($order['line_items']) && is_array($order['line_items'])) {
                foreach ($order['line_items'] as $item) {
                    // Filtra shipping e outros itens não-produto
                    if ($item['sku'] != 'PRIORITY+INSUREDSHIPPING!' && 
                        !str_contains(strtolower($item['name'] ?? ''), 'shipping') &&
                        !str_contains(strtolower($item['title'] ?? ''), 'shipping')) {
                        
                        $productName = $item['name'] ?? 'Unknown Product';
                        $quantity = $item['quantity'] ?? 1;
                        $revenue = isset($item['local_currency_item_total_price']) ? 
                            self::parseCurrency($item['local_currency_item_total_price']) : 0;
                        
                        if (!$products->has($productName)) {
                            $products->put($productName, [
                                'name' => $productName,
                                'total_quantity' => 0,
                                'total_revenue' => 0,
                                'sku' => $item['sku'] ?? ''
                            ]);
                        }
                        
                        $current = $products->get($productName);
                        $current['total_quantity'] += $quantity;
                        $current['total_revenue'] += $revenue;
                        $products->put($productName, $current);
                    }
                }
            }
        }

        $bestSeller = $products->sortByDesc('total_quantity')->first();

        if (!$bestSeller) {
            return [
                'product_name' => 'No products found',
                'total_quantity' => 0,
                'total_revenue' => 0,
                'total_revenue_formatted' => '$ 0.00',
                'sku' => '',
                'success' => true
            ];
        }

        return [
            'product_name' => $bestSeller['name'],
            'total_quantity' => $bestSeller['total_quantity'],
            'total_revenue' => $bestSeller['total_revenue'],
            'total_revenue_formatted' => '$ ' . number_format($bestSeller['total_revenue'], 2),
            'sku' => $bestSeller['sku'],
            'success' => true
        ];
    } catch (\Exception $e) {
        Log::error('Error getting best selling product: ' . $e->getMessage());
        return [
            'product_name' => 'Error loading product',
            'total_quantity' => 0,
            'total_revenue' => 0,
            'total_revenue_formatted' => '$ 0.00',
            'sku' => '',
            'success' => false
        ];
    }
}

    /**
     * 7. Tabela de Pedidos
     */
    public static function getOrdersTable(int $perPage = 10, int $page = 1): array
    {
        try {
            $orders = self::getOrdersCollection();
            
            // Paginação manual
            $total = $orders->count();
            $offset = ($page - 1) * $perPage;
            $paginatedOrders = $orders->slice($offset, $perPage);
            
            $formattedOrders = $paginatedOrders->map(function($order) {
                return [
                    'id' => $order['id'] ?? 'N/A',
                    'order_number' => $order['order_number'] ?? $order['name'] ?? 'N/A',
                    'customer_name' => ($order['customer']['first_name'] ?? '') . ' ' . ($order['customer']['last_name'] ?? ''),
                    'customer_email' => $order['contact_email'] ?? $order['email'] ?? 'N/A',
                    'financial_status' => self::getFinancialStatusText($order['financial_status'] ?? ''),
                    'fulfillment_status' => $order['fulfillment_status'] ?? 'N/A',
                    'amount' => $order['local_currency_amount'] ?? '0.00',
                    'amount_float' => isset($order['local_currency_amount']) ? 
                        self::parseCurrency($order['local_currency_amount']) : 0,
                    'created_at' => $order['created_at'] ?? 'N/A',
                    'formatted_date' => isset($order['created_at']) ? 
                        \Carbon\Carbon::parse($order['created_at'])->format('d/m/Y H:i') : 'N/A'
                ];
            });

            return [
                'orders' => $formattedOrders->values(),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ],
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::error('Error getting orders table: ' . $e->getMessage());
            return [
                'orders' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 1
                ],
                'success' => false
            ];
        }
    }

    /**
     * Helper methods
     */
    private static function parseCurrency(string $value): float
    {
        $cleaned = str_replace(',', '', $value);
        return (float) $cleaned;
    }

    private static function getFinancialStatusText($status): string
    {
        $statusMap = [
            1 => 'pending',
            2 => 'authorized', 
            3 => 'paid',
            4 => 'partially_paid',
            5 => 'refunded',
            6 => 'voided'
        ];

        return $statusMap[$status] ?? (string) $status;
    }

    /**
 * 9. Top 5 Produtos Mais Vendidos
 */
public static function getTop5Products(): array
{
    try {
        $orders = self::getOrdersCollection();
        
        $products = collect();
        
        foreach ($orders as $order) {
            if (isset($order['line_items']) && is_array($order['line_items'])) {
                foreach ($order['line_items'] as $item) {
                    // Filtra shipping e outros itens não-produto
                    if ($item['sku'] != 'PRIORITY+INSUREDSHIPPING!' && 
                        !str_contains(strtolower($item['name'] ?? ''), 'shipping') &&
                        !str_contains(strtolower($item['title'] ?? ''), 'shipping')) {
                        
                        $productName = $item['name'] ?? 'Unknown Product';
                        $quantity = $item['quantity'] ?? 1;
                        $revenue = isset($item['local_currency_item_total_price']) ? 
                            self::parseCurrency($item['local_currency_item_total_price']) : 0;
                        
                        if (!$products->has($productName)) {
                            $products->put($productName, [
                                'name' => $productName,
                                'total_quantity' => 0,
                                'total_revenue' => 0,
                                'sku' => $item['sku'] ?? '',
                                'vendor' => $item['vendor'] ?? ''
                            ]);
                        }
                        
                        $current = $products->get($productName);
                        $current['total_quantity'] += $quantity;
                        $current['total_revenue'] += $revenue;
                        $products->put($productName, $current);
                    }
                }
            }
        }

        $topProducts = $products->sortByDesc('total_quantity')
                               ->take(5)
                               ->map(function($product) {
                                   return [
                                       'name' => $product['name'],
                                       'total_quantity' => $product['total_quantity'],
                                       'total_revenue' => $product['total_revenue'],
                                       'total_revenue_formatted' => '$ ' . number_format($product['total_revenue'], 2),
                                       'sku' => $product['sku'],
                                       'vendor' => $product['vendor']
                                   ];
                               })
                               ->values();

        return [
            'top_products' => $topProducts,
            'success' => true
        ];
    } catch (\Exception $e) {
        Log::error('Error getting top 5 products: ' . $e->getMessage());
        return [
            'top_products' => [],
            'success' => false
        ];
    }
}

/**
 * 10. Análise de Upsell
 */
public static function getUpsellAnalysis(): array
{
    try {
        $orders = self::getOrdersCollection();
        
        $upsellData = [
            'total_orders' => 0,
            'orders_with_upsell' => 0,
            'upsell_rate' => 0,
            'total_upsell_revenue' => 0,
            'upsell_products' => collect()
        ];

        foreach ($orders as $order) {
            $upsellData['total_orders']++;
            
            $hasUpsell = false;
            $orderUpsellRevenue = 0;

            if (isset($order['line_items']) && is_array($order['line_items'])) {
                foreach ($order['line_items'] as $item) {
                    // Identifica upsells (geralmente tem up_sell_id ou estão em posições específicas)
                    $isUpsell = isset($item['up_sell_id']) && $item['up_sell_id'] > 0 ||
                                isset($item['up_sell_type']) && !empty($item['up_sell_type']) ||
                                str_contains(strtolower($item['name'] ?? ''), 'upsell') ||
                                str_contains(strtolower($item['title'] ?? ''), 'upsell');

                    if ($isUpsell) {
                        $hasUpsell = true;
                        $revenue = isset($item['local_currency_item_total_price']) ? 
                            self::parseCurrency($item['local_currency_item_total_price']) : 0;
                        $orderUpsellRevenue += $revenue;

                        // Coleta dados do produto de upsell
                        $productName = $item['name'] ?? 'Unknown Upsell Product';
                        if (!$upsellData['upsell_products']->has($productName)) {
                            $upsellData['upsell_products']->put($productName, [
                                'name' => $productName,
                                'count' => 0,
                                'total_revenue' => 0,
                                'up_sell_type' => $item['up_sell_type'] ?? 'unknown'
                            ]);
                        }

                        $current = $upsellData['upsell_products']->get($productName);
                        $current['count']++;
                        $current['total_revenue'] += $revenue;
                        $upsellData['upsell_products']->put($productName, $current);
                    }
                }
            }

            if ($hasUpsell) {
                $upsellData['orders_with_upsell']++;
                $upsellData['total_upsell_revenue'] += $orderUpsellRevenue;
            }
        }

        // Calcula taxa de upsell
        $upsellData['upsell_rate'] = $upsellData['total_orders'] > 0 ? 
            ($upsellData['orders_with_upsell'] / $upsellData['total_orders']) * 100 : 0;

        // Ordena produtos de upsell por revenue
        $topUpsellProducts = $upsellData['upsell_products']
            ->sortByDesc('total_revenue')
            ->take(5)
            ->map(function($product) {
                return [
                    'name' => $product['name'],
                    'count' => $product['count'],
                    'total_revenue' => $product['total_revenue'],
                    'total_revenue_formatted' => '$ ' . number_format($product['total_revenue'], 2),
                    'up_sell_type' => $product['up_sell_type']
                ];
            })
            ->values();

        return [
            'total_orders' => $upsellData['total_orders'],
            'orders_with_upsell' => $upsellData['orders_with_upsell'],
            'upsell_rate' => round($upsellData['upsell_rate'], 2),
            'total_upsell_revenue' => $upsellData['total_upsell_revenue'],
            'total_upsell_revenue_formatted' => '$ ' . number_format($upsellData['total_upsell_revenue'], 2),
            'avg_upsell_value' => $upsellData['orders_with_upsell'] > 0 ? 
                $upsellData['total_upsell_revenue'] / $upsellData['orders_with_upsell'] : 0,
            'top_upsell_products' => $topUpsellProducts,
            'success' => true
        ];
    } catch (\Exception $e) {
        Log::error('Error getting upsell analysis: ' . $e->getMessage());
        return [
            'total_orders' => 0,
            'orders_with_upsell' => 0,
            'upsell_rate' => 0,
            'total_upsell_revenue' => 0,
            'total_upsell_revenue_formatted' => '$ 0.00',
            'avg_upsell_value' => 0,
            'top_upsell_products' => [],
            'success' => false
        ];
    }
}
/**
 * 11. Top 10 Cidades
 */
public static function getTop10Cities(): array
{
    try {
        $orders = self::getOrdersCollection();
        
        $cities = collect();
        
        foreach ($orders as $order) {
            // Tenta pegar a cidade do shipping address primeiro, depois billing
            $city = $order['shipping_address']['city'] ?? 
                    $order['billing_address']['city'] ?? 
                    'Unknown City';
            
            $state = $order['shipping_address']['province_code'] ?? 
                    $order['billing_address']['province_code'] ?? 
                    $order['shipping_address']['province'] ?? 
                    $order['billing_address']['province'] ?? 
                    'Unknown State';
            
            $country = $order['shipping_address']['country_code'] ?? 
                      $order['billing_address']['country_code'] ?? 
                      $order['shipping_address']['country'] ?? 
                      $order['billing_address']['country'] ?? 
                      'Unknown Country';

            $cityKey = $city . ', ' . $state . ', ' . $country;
            
            if (!$cities->has($cityKey)) {
                $cities->put($cityKey, [
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'order_count' => 0,
                    'total_revenue' => 0,
                    'customers' => collect()
                ]);
            }
            
            $current = $cities->get($cityKey);
            $current['order_count']++;
            
            $revenue = isset($order['local_currency_amount']) ? 
                self::parseCurrency($order['local_currency_amount']) : 0;
            $current['total_revenue'] += $revenue;
            
            // Conta clientes únicos por cidade
            $customerId = $order['customer_id'] ?? null;
            if ($customerId && !$current['customers']->contains($customerId)) {
                $current['customers']->push($customerId);
            }
            
            $cities->put($cityKey, $current);
        }

        $topCities = $cities->sortByDesc('order_count')
                           ->take(10)
                           ->map(function($cityData, $cityKey) {
                               return [
                                   'city' => $cityData['city'],
                                   'state' => $cityData['state'],
                                   'country' => $cityData['country'],
                                   'order_count' => $cityData['order_count'],
                                   'total_revenue' => $cityData['total_revenue'],
                                   'total_revenue_formatted' => '$ ' . number_format($cityData['total_revenue'], 2),
                                   'unique_customers' => $cityData['customers']->count(),
                                   'avg_order_value' => $cityData['order_count'] > 0 ? 
                                       $cityData['total_revenue'] / $cityData['order_count'] : 0
                               ];
                           })
                           ->values();

        return [
            'top_cities' => $topCities,
            'success' => true
        ];
    } catch (\Exception $e) {
        Log::error('Error getting top 10 cities: ' . $e->getMessage());
        return [
            'top_cities' => [],
            'success' => false
        ];
    }
}
}