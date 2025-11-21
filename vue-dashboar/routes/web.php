<?php

use App\Http\Controllers\DashboardMetricsController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::get('/metrics', [DashboardMetricsController::class, 'index']);
Route::get('/metrics/debug', [DashboardMetricsController::class, 'debug']);

Route::get('/metrics/delivery', [MetricsController::class, 'deliveryMetrics']);
Route::get('/metrics/total-orders', [MetricsController::class, 'totalOrders']);//
Route::get('/metrics/total-revenue', [MetricsController::class, 'totalRevenue']);//
Route::get('/metrics/unique-customers', [MetricsController::class, 'uniqueCustomers']);
Route::get('/metrics/financial-summary', [MetricsController::class, 'financialSummary']);
Route::get('/metrics/refund-rate', [MetricsController::class, 'refundRate']);
Route::get('/metrics/best-selling-product', [MetricsController::class, 'bestSellingProduct']);
Route::get('/metrics/orders-table', [MetricsController::class, 'ordersTable']);
Route::get('/metrics/top-5-products', [MetricsController::class, 'top5Products']);
Route::get('/metrics/upsell-analysis', [MetricsController::class, 'upsellAnalysis']);
Route::get('/metrics/top-10-cities', [MetricsController::class, 'top10Cities']);
require __DIR__.'/auth.php';
