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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique coupon code
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            
            // Validity
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            
            // Usage tracking
            $table->integer('usage_limit')->nullable(); // Total usage limit for this coupon
            $table->integer('usage_limit_per_customer')->nullable(); // Per customer
            $table->integer('times_used')->default(0); // Track total usage
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
