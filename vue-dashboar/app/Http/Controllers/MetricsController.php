<?php

namespace App\Http\Controllers;

use App\Services\MetricDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    /**
     * Endpoint para métricas de entrega (JSON para Vue)
     */
    public function deliveryMetrics(): JsonResponse
    {
        $metrics = MetricDataService::getDeliveryMetrics('https://dev-crm.ogruposix.com/candidato-teste-pratico-backend-dashboard/test-orders');
        
        return response()->json($metrics);
    }

        /**
     * 1. Total de Pedidos
     */
    public function totalOrders(): JsonResponse
    {
        return response()->json(MetricDataService::getTotalOrders());
    }

    /**
     * 2. Receita Total
     */
    public function totalRevenue(): JsonResponse
    {
        return response()->json(MetricDataService::getTotalRevenue());
    }

    /**
     * 3. Clientes Únicos
     */
    public function uniqueCustomers(): JsonResponse
    {
        return response()->json(MetricDataService::getUniqueCustomers());
    }

    /**
     * 4. Resumo Financeiro
     */
    public function financialSummary(): JsonResponse
    {
        return response()->json(MetricDataService::getFinancialSummary());
    }

    /**
     * 5. Taxa de Reembolso
     */
    public function refundRate(): JsonResponse
    {
        return response()->json(MetricDataService::getRefundRate());
    }

    /**
     * 6. Produto Mais Vendido
     */
    public function bestSellingProduct(): JsonResponse
    {
        return response()->json(MetricDataService::getBestSellingProduct());
    }

    /**
     * 7. Tabela de Pedidos
     */
    public function ordersTable(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        return response()->json(MetricDataService::getOrdersTable($perPage, $page));
    }

}