<?php

use App\Http\Controllers\AgingpiutController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\Dashboard2Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemografiController;
use App\Http\Controllers\EasyController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\LabarController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SektorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.dash')->group(function () {
    Route::get('dashboard1', [DashboardController::class, 'index'])->name('dashboard1');
    Route::post('dashboard1/detail1', [DashboardController::class, 'sektorDetail'])->name('dashboard1.detail1');
    Route::post('dashboard1/penjHari', [DashboardController::class, 'penjualanHariIni'])->name('dashboard1.penjHari');
    Route::post('dashboard1/detail_aging', [DashboardController::class, 'agingDetail'])->name('dashboard1.detail_aging');
    Route::post('dashboard1/detail2', [DashboardController::class, 'fakturDetail'])->name('dashboard1.detail2');

    Route::get('profile/company', [ProfileController::class, 'company'])->name('profile.company');
    Route::get('profile/user', [ProfileController::class, 'user'])->name('profile.user');

    Route::get('sales/subsidi', [SalesController::class, 'subsidi'])->name('sales.subsidi');
    Route::get('sales/nonsub', [SalesController::class, 'nonsub'])->name('sales.nonsub');
    Route::get('sales/kimia', [SalesController::class, 'kimia'])->name('sales.kimia');
    Route::get('sales/angkutan', [SalesController::class, 'angkutan'])->name('sales.angkutan');

    Route::get('financial/balance_sheet', [FinancialController::class, 'balanceSheet'])->name('financial.balance_sheet');
    Route::get('financial/income_statement', [FinancialController::class, 'incomeStatement'])->name('financial.income_statement');
    Route::get('financial/cash_flow', [FinancialController::class, 'cashFlow'])->name('financial.cash_flow');
    Route::get('financial/sales_stock', [FinancialController::class, 'salesStock'])->name('financial.sales_stock');

    Route::get('easy', [EasyController::class, 'index'])->name('easy.index');

    Route::get('anggaran', [AnggaranController::class, 'index'])->name('anggaran.index');

    Route::get('demografi', [DemografiController::class, 'index'])->name('demografi.index');

    Route::get('agingpiut', [AgingpiutController::class, 'index'])->name('agingpiut.index');
    Route::post('agingpiut/detail_aging', [AgingpiutController::class, 'detailAging'])->name('agingpiut.detail_aging');
    Route::post('agingpiut/detail2', [AgingpiutController::class, 'detail2'])->name('agingpiut.detail2');
    Route::post('agingpiut/detail3', [AgingpiutController::class, 'detail3'])->name('agingpiut.detail3');
    Route::post('agingpiut/detail4', [AgingpiutController::class, 'detail4'])->name('agingpiut.detail4');
    Route::post('agingpiut/detail5', [AgingpiutController::class, 'detail5'])->name('agingpiut.detail5');

    Route::get('penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::post('penjualan/chart2', [PenjualanController::class, 'chart2'])->name('penjualan.chart2');
    Route::post('penjualan/chart3', [PenjualanController::class, 'chart3'])->name('penjualan.chart3');
    Route::post('penjualan/chart_detail', [PenjualanController::class, 'chartDetail'])->name('penjualan.chart_detail');
    Route::post('penjualan/detail1', [PenjualanController::class, 'detail1'])->name('penjualan.detail1');
    Route::post('penjualan/detail2', [PenjualanController::class, 'detail2'])->name('penjualan.detail2');

    Route::get('sektor', [SektorController::class, 'index'])->name('sektor.index');
    Route::post('sektor/detail1', [SektorController::class, 'detail1'])->name('sektor.detail1');

    Route::get('cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('cash/detail1', [CashController::class, 'detail1'])->name('cash.detail1');

    Route::get('labar', [LabarController::class, 'index'])->name('labar.index');
    Route::post('labar/detail1', [LabarController::class, 'detail1'])->name('labar.detail1');
    Route::post('labar/detail2', [LabarController::class, 'detail2'])->name('labar.detail2');

    Route::get('dashboard2', [Dashboard2Controller::class, 'index'])->name('dashboard2');
    Route::post('dashboard2/detail1', [Dashboard2Controller::class, 'sektorDetail'])->name('dashboard2.detail1');
    Route::post('dashboard2/penjHari', [Dashboard2Controller::class, 'penjualanHariIni'])->name('dashboard2.penjHari');
    Route::post('dashboard2/detail_aging', [Dashboard2Controller::class, 'agingDetail'])->name('dashboard2.detail_aging');
    Route::post('dashboard2/detail2', [Dashboard2Controller::class, 'fakturDetail'])->name('dashboard2.detail2');
    Route::post('dashboard2/chart_detail', [Dashboard2Controller::class, 'chartDetail'])->name('dashboard2.chart_detail');
});
