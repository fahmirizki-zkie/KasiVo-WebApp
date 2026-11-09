<?php

use App\Livewire\BusinessOnboarding;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Onboarding Profil Usaha (Task KV-010)
Route::get('onboarding', BusinessOnboarding::class)
    ->middleware(['auth'])
    ->name('onboarding');

// Navigasi Fitur Utama KasiVo (Task KV-008)
Route::middleware(['auth'])->group(function () {
    Route::get('transactions/create-pengeluaran', function () {
        return view('placeholder', [
            'title' => 'Catat Pengeluaran',
            'description' => 'Jalur pencatatan belanja bahan baku dan operasional via foto nota, suara, atau teks bebas.'
        ]);
    })->name('transactions.expense');

    Route::get('transactions/create-penjualan', function () {
        return view('placeholder', [
            'title' => 'Catat Penjualan',
            'description' => 'Jalur pencatatan omzet harian atau impor riwayat penjualan digital.'
        ]);
    })->name('transactions.income');

    Route::get('transactions', function () {
        return view('placeholder', [
            'title' => 'Riwayat Transaksi',
            'description' => 'Daftar riwayat transaksi kas masuk dan kas keluar usaha.'
        ]);
    })->name('transactions.index');

    Route::get('restock', function () {
        return view('placeholder', [
            'title' => 'Saran Restock Bahan',
            'description' => 'Rekomendasi pembelian bahan baku berbasis pola belanja mingguan.'
        ]);
    })->name('restock');

    Route::get('business-profile', function () {
        return view('placeholder', [
            'title' => 'Profil Usaha & Ekspor PDF',
            'description' => 'Ringkasan performa usaha dan ekspor laporan PDF untuk akses pembiayaan.'
        ]);
    })->name('business-profile');
});

require __DIR__.'/auth.php';
