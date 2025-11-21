<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
class ImportDataService
{
    public function import(string $url, array $options = []): ?array
    {
        $timeout = $options['timeout'] ?? 30;
        $cacheMinutes = $options['cache'] ?? null;
        $retry = $options['retry'] ?? 3;

        //  Cache Key segura e única
        $cacheKey = 'import_data:' . md5($url . serialize($options));

        //  NOVO: tentamos recuperar do cache com tags
        if ($cacheMinutes) {
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                Log::info(" Cache HIT para URL: {$url}");
                return $cached;
            }
        }

        try {
            $request = Http::timeout($timeout)
                ->retry($retry, 1000)
                ->withOptions(['verify' => false]);

            if (!empty($options['headers']) && is_array($options['headers'])) {
                $request = $request->withHeaders($options['headers']);
            }

            $response = $request->get($url);

            if ($response->successful()) {
                $data = $response->json();

                //  NOVO: salva com tags
                if ($cacheMinutes && $data) {
                    Cache::put($cacheKey, $data, now()->addMinutes($cacheMinutes));
                    Log::info(" Cache armazenado por {$cacheMinutes} min para: {$url}");
                }

                Log::info(" Importação OK", [
                    'status_code' => $response->status()
                ]);

                return $data;
            }

            Log::error(" HTTP Error", [
                'status_code' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error(" Exception", [
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ]);

            return null;
        }
    }
    /**
     * Extract orders from the response data
     */
    private static function extractOrders(array $data): array
    {
        // Estrutura: {"orders": [{"order": {...}}, {"order": {...}}]}
        if (isset($data['orders']) && is_array($data['orders'])) {
            $orders = [];
            foreach ($data['orders'] as $item) {
                if (isset($item['order']) && is_array($item['order'])) {
                    $orders[] = $item['order'];
                } else {
                    // Se não tem estrutura "order", usa o item diretamente
                    $orders[] = $item;
                }
            }
            return $orders;
        }

        // Se for um array direto de orders
        if (isset($data[0]) && isset($data[0]['order'])) {
            return array_map(function($item) {
                return $item['order'];
            }, $data);
        }

        // Se for um array direto sem wrapper
        if (isset($data[0]) && isset($data[0]['id'])) {
            return $data;
        }

        // Se não encontrou estrutura conhecida, retorna vazio
        return [];
    }

    /**
     * Static helper to import and process fetched JSON with detailed debugging
     */
    public static function process(string $url, callable $callback = null, array $options = [])
    {
        $service = new self();
        
        // Debug: show request details
        if (app()->hasDebugModeEnabled()) {
            dump("🔄 Iniciando importação de dados...");
            dump("📡 URL: {$url}");
            dump("⚙️ Options:", $options);
        }

        $data = $service->import($url, $options);

        if ($data === null) {
            Log::warning("Failed to import data from URL: {$url}");
            if (app()->hasDebugModeEnabled()) {
                dump(" Falha na importação dos dados");
            }
            return null;
        }

        // Debug: show data structure
        if (app()->hasDebugModeEnabled()) {
            dump(" Dados importados com sucesso!");
            dump(" Tipo de dados: " . gettype($data));
            dump(" Estrutura raiz:", array_keys($data));
            
            if (isset($data['orders'])) {
                dump("📦 Encontrado key 'orders' com " . count($data['orders']) . " items");
                
                if (!empty($data['orders']) && isset($data['orders'][0])) {
                    $firstOrderItem = $data['orders'][0];
                    dump(" Estrutura do primeiro item em 'orders':", array_keys($firstOrderItem));
                    
                    if (isset($firstOrderItem['order'])) {
                        $firstOrder = $firstOrderItem['order'];
                        dump(" Primeiro order ID: " . ($firstOrder['id'] ?? 'N/A'));
                        dump(" Email: " . ($firstOrder['email'] ?? 'N/A'));
                    }
                }
            }
        }

        if ($callback === null) {
            return $data;
        }

        // Extrai os orders da estrutura
        $orders = self::extractOrders($data);
        
        if (app()->hasDebugModeEnabled()) {
            dump(" Orders extraídos: " . count($orders));
        }

        if (empty($orders)) {
            if (app()->hasDebugModeEnabled()) {
                dump(" Nenhum order encontrado na estrutura");
            }
            return [];
        }

        $results = [];
        foreach ($orders as $key => $order) {
            $results[$key] = $callback($order, $key);
            
            // Debug: show progress for large datasets
            if (app()->hasDebugModeEnabled() && count($orders) > 50) {
                if ($key % 50 === 0) {
                    dump(" Processados {$key}/" . count($orders) . " orders...");
                }
            }
        }
        
        if (app()->hasDebugModeEnabled()) {
            dump(" Processamento concluído!");
            dump(" Total de orders processados: " . count($results));
        }
        
        return $results;
    }

    /**
     * Specialized method for processing orders with Laravel collections
     */
    public static function processOrders(string $url, callable $callback = null, array $options = [])
    {
        return self::process($url, $callback, $options);
    }


    /**
     * Debug method with detailed Laravel-specific output
     */
    public static function debugOrders(string $url, array $options = [])
    {
        dump(" === DEBUG ORDER IMPORT ===");
        
        $service = new self();
        $data = $service->import($url, $options);

        if ($data === null) {
            dump(" FALHA: Não foi possível importar dados");
            return null;
        }

        dump(" SUCESSO: Dados importados");
        dump(" Tipo: " . gettype($data));
        dump(" Estrutura raiz:", array_keys($data));
        
        if (isset($data['orders'])) {
            $orders = $data['orders'];
            dump("📦 Key 'orders' encontrada com " . count($orders) . " items");
            
            if (!empty($orders) && isset($orders[0])) {
                $firstItem = $orders[0];
                dump(" Estrutura do primeiro item em 'orders':", array_keys($firstItem));
                
                if (isset($firstItem['order'])) {
                    $firstOrder = $firstItem['order'];
                    dump(" Order ID: " . ($firstOrder['id'] ?? 'N/A'));
                    dump(" Email: " . ($firstOrder['email'] ?? 'N/A'));
                    dump("💰 Total: " . ($firstOrder['total_price'] ?? 'N/A'));
                    dump("📦 Line items: " . count($firstOrder['line_items'] ?? []));
                    dump("🏷️ Status: " . ($firstOrder['financial_status'] ?? 'N/A'));
                }
            }
            
            // Extrai e conta orders válidos
            $extractedOrders = self::extractOrders($data);
            dump(" Total de orders extraídos: " . count($extractedOrders));
            
            if (!empty($extractedOrders)) {
                dump(" Primeiros 3 order IDs:");
                $sampleIds = collect($extractedOrders)->take(3)->pluck('id');
                dump($sampleIds->toArray());
            }
        } else {
            dump(" Estrutura 'orders' não encontrada");
            dump(" Estrutura completa disponível:", array_keys($data));
        }

        dump(" === FIM DEBUG ===");

        return $data;
    }

    /**
     * Get orders with pagination support
     */
    public static function getOrdersWithPagination(string $baseUrl, array $options = [], int $maxPages = 10)
    {
        $allOrders = [];
        $page = 1;
        
        while ($page <= $maxPages) {
            $url = $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . "page=$page";
            
            dump(" Buscando página $page: $url");
            
            $service = new self();
            $data = $service->import($url, $options);
            
            if ($data === null || !isset($data['orders']) || empty($data['orders'])) {
                dump(" Fim dos dados na página $page");
                break;
            }
            
            $orders = self::extractOrders($data);
            $allOrders = array_merge($allOrders, $orders);
            
            dump(" Página $page: " . count($orders) . " orders");
            
            $page++;
            
            // Pequena pausa para não sobrecarregar o servidor
            usleep(500000); // 500ms
        }
        
        dump(" Total de orders coletados: " . count($allOrders));
        return $allOrders;
    }

     /**
     * Process orders and return as Laravel Collection with formatted values
     */
    public static function importOrdersAsCollection(string $url, array $options = [])
    {
        $service = new self();
        $data = $service->import($url, $options);
        
        if ($data === null) {
            return collect();
        }

        $orders = self::extractOrders($data);
        
        return collect($orders)->map(function ($order) {
            return self::formatOrderValues($order);
        });
    }

    /**
     * Format order values to proper types
     */
    private static function formatOrderValues(array $order): array
    {
        // Converte total_price de string "2,274.47" para float 2274.47
        if (isset($order['total_price']) && is_string($order['total_price'])) {
            $order['total_price_float'] = self::parseCurrency($order['total_price']);
        }

        // Converte outros campos numéricos se necessário
        if (isset($order['current_total_price']) && is_string($order['current_total_price'])) {
            $order['current_total_price_float'] = self::parseCurrency($order['current_total_price']);
        }

        if (isset($order['subtotal_price']) && is_string($order['subtotal_price'])) {
            $order['subtotal_price_float'] = self::parseCurrency($order['subtotal_price']);
        }

        // Converte financial_status para string legível se for numérico
        if (isset($order['financial_status'])) {
            $order['financial_status_text'] = self::getFinancialStatusText($order['financial_status']);
        }

        return $order;
    }

    /**
     * Parse currency string to float
     */
    private static function parseCurrency(string $value): float
    {
        // Remove vírgulas de milhares e converte para float
        $cleaned = str_replace(',', '', $value);
        return (float) $cleaned;
    }

    /**
     * Convert financial status code to text
     */
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
     * Get dashboard metrics from orders
     */
    public static function getDashboardMetrics(string $url, array $options = [])
    {
        $ordersCollection = self::importOrdersAsCollection($url, $options);

        if ($ordersCollection->isEmpty()) {
            return [
                'total_orders' => 0,
                'total_revenue' => 0,
                'unique_customers' => 0,
                'average_order_value' => 0,
                'orders_by_status' => []
            ];
        }

        // Usa os campos convertidos para cálculos
        $totalRevenue = $ordersCollection->sum('total_price_float');
        $uniqueCustomers = $ordersCollection->pluck('email')->unique()->count();
        $averageOrderValue = $totalRevenue / $ordersCollection->count();

        // Agrupa por status
        $ordersByStatus = $ordersCollection->groupBy('financial_status_text')
            ->map->count();

        return [
            'total_orders' => $ordersCollection->count(),
            'total_revenue' => $totalRevenue,
            'unique_customers' => $uniqueCustomers,
            'average_order_value' => $averageOrderValue,
            'orders_by_status' => $ordersByStatus,
            'sample_data' => $ordersCollection->take(3)->map(function($order) {
                return [
                    'id' => $order['id'],
                    'email' => $order['email'],
                    'total_price_original' => $order['total_price'] ?? 'N/A',
                    'total_price_float' => $order['total_price_float'] ?? 0,
                    'status' => $order['financial_status_text'] ?? 'N/A'
                ];
            })
        ];
    }
}