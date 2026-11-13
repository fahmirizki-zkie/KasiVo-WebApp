<?php

use App\Livewire\BusinessOnboarding;
use App\Livewire\CreateExpensePhotoTransaction;
use App\Livewire\CreateExpenseTextTransaction;
use App\Livewire\CreateExpenseVoiceTransaction;
use App\Livewire\CreateIncomeTransaction;
use App\Livewire\Dashboard;
use App\Livewire\ImportDigitalSales;
use App\Livewire\SelectTransactionType;
use App\Livewire\TransactionHistory;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Onboarding Profil Usaha (Task KV-010)
Route::get('onboarding', BusinessOnboarding::class)
    ->middleware(['auth'])
    ->name('onboarding');

// Navigasi Fitur Utama KasiVo (Task KV-008 & KV-011)
Route::middleware(['auth'])->group(function () {
    Route::get('transactions/create', SelectTransactionType::class)->name('transactions.create');

    Route::get('transactions/create-pengeluaran', CreateExpenseVoiceTransaction::class)->name('transactions.expense');

    Route::get('transactions/create-teks', CreateExpenseTextTransaction::class)->name('transactions.text');

    Route::get('transactions/create-foto', CreateExpensePhotoTransaction::class)->name('transactions.photo');

    Route::get('transactions/create-penjualan', CreateIncomeTransaction::class)->name('transactions.income');

    Route::get('transactions/import-digital', ImportDigitalSales::class)->name('transactions.import-digital');

    Route::get('transactions', TransactionHistory::class)->name('transactions.index');

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
