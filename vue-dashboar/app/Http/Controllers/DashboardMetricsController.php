<?php

namespace App\Http\Controllers;

use App\Services\ImportDataService;
use Illuminate\Http\Request;

class DashboardMetricsController extends Controller
{
    public function index()
    {
        try {
            $metrics = ImportDataService::getDashboardMetrics(
                'https://dev-crm.ogruposix.com/candidato-teste-pratico-backend-dashboard/test-orders',
                [
                    'timeout' => 30,
                    'cache' => 10 // cache por 10 minutos
                ]
            );

            // Debug: mostra os dados
            dump("📊 Métricas calculadas:");
            dump($metrics);

            return view('dashboard.metrics', compact('metrics'));
        } catch (\Exception $e) {
            dump("❌ Erro: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load metrics'], 500);
        }
    }

    public function debug()
    {
        // // Para debugging detalhado
        // $debugData = ImportDataService::debugOrders(
        //     'https://dev-crm.ogruposix.com/candidato-teste-pratico-backend-dashboard/test-orders'
        // );

        $ordersCollection = ImportDataService::importOrdersAsCollection(
            'https://dev-crm.ogruposix.com/candidato-teste-pratico-backend-dashboard/test-orders',
            [
                'cache' => 10 // minutos
            ]
        );

        $ordersCollection = $ordersCollection->take(100);
        $customers = $ordersCollection->pluck('customer_id')->toArray();

        // Agora você pode usar todos os métodos da Collection
        $totalValueBR = $ordersCollection->sum('total_price_float');
        $totalValueUS = $ordersCollection->sum('local_currency_amount');
        $uniqueCustomers = $ordersCollection->pluck('customer_id')->unique()->count();

        dump("💰 Valor total(BR): " . $totalValueBR);
        dump("💰 Valor total(US): " . $totalValueUS);
        dump("👥 Clientes únicos: " . $uniqueCustomers);
        dump("📦 Total de orders: " . $ordersCollection->count());

        // Teste de soma
        $total = $ordersCollection->sum('total_price_float');
        dump("💰 Total revenue (sum test): " . $total);

        return ;
    }
}
