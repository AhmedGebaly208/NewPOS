<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'mobile_money', 'bank_transfer', 'other']);
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable()->comment('Transaction reference for electronic payments');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['sale_id', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
