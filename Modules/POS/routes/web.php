<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Livewire\POSInterface;
use Modules\POS\Http\Controllers\POSController;

Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', POSInterface::class)->name('index');
    Route::get('/receipt/{sale}', [POSController::class, 'viewReceipt'])->name('receipt.view');
    Route::get('/receipt/{sale}/print', [POSController::class, 'printReceipt'])->name('receipt.print');
});
