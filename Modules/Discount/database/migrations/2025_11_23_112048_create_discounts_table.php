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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Discount Type: percentage, fixed, buy_x_get_y
            $table->enum('type', ['percentage', 'fixed', 'buy_x_get_y'])->default('percentage');
            
            // Discount Value
            $table->decimal('value', 10, 2)->default(0); // For percentage or fixed amount
            
            // Buy X Get Y fields
            $table->integer('buy_quantity')->nullable(); // Buy X
            $table->integer('get_quantity')->nullable(); // Get Y
            
            // Application scope
            $table->enum('applies_to', ['all', 'specific_products', 'specific_categories'])->default('all');
            
            // Conditions
            $table->decimal('minimum_purchase', 10, 2)->nullable(); // Minimum purchase amount
            $table->integer('minimum_quantity')->nullable(); // Minimum items quantity
            
            // Customer restrictions
            $table->enum('customer_eligibility', ['all', 'specific_customers', 'customer_tier'])->default('all');
            $table->json('eligible_customer_tiers')->nullable(); // ['bronze', 'silver', 'gold', 'platinum']
            
            // Date restrictions
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            
            // Usage limits
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('usage_limit_per_customer')->nullable(); // Per customer usage limit
            $table->integer('times_used')->default(0); // Track usage
            
            // Status and priority
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority discounts apply first
            $table->boolean('is_combinable')->default(false); // Can combine with other discounts
            
            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('type');
            $table->index('is_active');
            $table->index(['starts_at', 'ends_at']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
