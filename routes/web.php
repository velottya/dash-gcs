<?php

use App\Http\Controllers\AgingpiutController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemografiController;
use App\Http\Controllers\EasyController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\LabarController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RkapController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SektorController;
use App\Http\Controllers\UserController;
use App\Support\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.dash')->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/detail1', [DashboardController::class, 'sektorDetail'])->name('dashboard.detail1');
    Route::post('dashboard/penjHari', [DashboardController::class, 'penjualanHariIni'])->name('dashboard.penjHari');
    Route::post('dashboard/detail_aging', [DashboardController::class, 'agingDetail'])->name('dashboard.detail_aging');
    Route::post('dashboard/detail2', [DashboardController::class, 'fakturDetail'])->name('dashboard.detail2');
    Route::post('dashboard/chart_detail', [DashboardController::class, 'chartDetail'])->name('dashboard.chart_detail');
    Route::post('dashboard/penjualan_breakdown', [DashboardController::class, 'penjualanBreakdown'])->name('dashboard.penjualan_breakdown');

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('profile/company', [ProfileController::class, 'company'])->name('profile.company');
    Route::post('profile/company', [ProfileController::class, 'updateCompany'])->name('profile.company.update');
    Route::get('profile/user', [ProfileController::class, 'user'])->name('profile.user');
    Route::post('profile/user', [ProfileController::class, 'updateUser'])->name('profile.user.update');
    Route::post('profile/user/change-password', [ProfileController::class, 'changePassword'])->name('profile.user.change-password');

    // ── Sales ─────────────────────────────────────────────────────────────────
    Route::get('sales/subsidi', [SalesController::class, 'subsidi'])->name('sales.subsidi');
    Route::get('sales/nonsub', [SalesController::class, 'nonsub'])->name('sales.nonsub');
    Route::get('sales/kimia', [SalesController::class, 'kimia'])->name('sales.kimia');
    Route::get('sales/angkutan', [SalesController::class, 'angkutan'])->name('sales.angkutan');

    // ── Financial ─────────────────────────────────────────────────────────────
    Route::get('financial/balance_sheet', [FinancialController::class, 'balanceSheet'])->name('financial.balance_sheet');
    Route::get('financial/income_statement', [FinancialController::class, 'incomeStatement'])->name('financial.income_statement');
    Route::get('financial/cash_flow', [FinancialController::class, 'cashFlow'])->name('financial.cash_flow');

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
    Route::post('penjualan/breakdown', [PenjualanController::class, 'breakdown'])->name('penjualan.breakdown');

    Route::get('sektor', [SektorController::class, 'index'])->name('sektor.index');
    Route::post('sektor/detail1', [SektorController::class, 'detail1'])->name('sektor.detail1');

    Route::get('cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('cash/detail1', [CashController::class, 'detail1'])->name('cash.detail1');

    Route::get('labar', [LabarController::class, 'index'])->name('labar.index');
    Route::post('labar/detail1', [LabarController::class, 'detail1'])->name('labar.detail1');
    Route::post('labar/detail2', [LabarController::class, 'detail2'])->name('labar.detail2');

    // ── RKAP (semua role yang login) ──────────────────────────────────────────
    Route::prefix('rkap')->name('rkap.')->group(function () {
        Route::get('/', [RkapController::class, 'index'])->name('index');
        Route::get('/detail-data/{id}', [RkapController::class, 'detail'])->name('detail');

        // Manager
        Route::middleware('role:' . Role::MANAGER)->group(function () {
            Route::get('/create', [RkapController::class, 'create'])->name('create');
            Route::post('/', [RkapController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [RkapController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RkapController::class, 'updateRkap'])->name('update');
            Route::delete('/{id}', [RkapController::class, 'destroy'])->name('destroy');
        });

        // GM: validasi
        Route::patch('/{id}/gm-validate', [RkapController::class, 'gmValidate'])
            ->middleware('role:' . Role::GM)
            ->name('gm-validate');

        // Direksi: pengesahan
        Route::patch('/{id}/direksi-validate', [RkapController::class, 'direksiValidate'])
            ->middleware('role:' . Role::DIREKSI)
            ->name('direksi-validate');

        // Export Excel (semua role)
        Route::get('/{id}/export', [RkapController::class, 'export'])->name('export');

        // Detail JSON (semua role)
        Route::get('/{id}/detail', [RkapController::class, 'detail'])->name('detail-json');
    });

    // ── User Management ───────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        // GM + Superadmin: view list & detail (GM hanya lihat managernya sendiri)
        Route::middleware('role:' . Role::SUPERADMIN . ',' . Role::GM)->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{nik}/detail', [UserController::class, 'detail'])->name('detail');
        });

        // Superadmin only: operasi CRUD
        Route::middleware('role:' . Role::SUPERADMIN)->group(function () {
            Route::get('/search-pegawai', [UserController::class, 'searchPegawai'])->name('search-pegawai');
            Route::get('/search-mst-user', [UserController::class, 'searchMstUser'])->name('search-mst-user');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{nik}', [UserController::class, 'update'])->name('update');
            Route::patch('/{nik}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
            Route::delete('/{nik}', [UserController::class, 'destroy'])->name('destroy');
        });
    });
});
