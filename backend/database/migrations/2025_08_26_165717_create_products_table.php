<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->integer('current_quantity')->default(0);
            $table->integer('min_stock_level')->default(0);
            $table->integer('max_stock_level')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('supplier_name');
            $table->string('storage_location')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->json('specifications')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('category_id');
            $table->index('sku');
            $table->index('status');
            $table->index('supplier_name');
            $table->index('storage_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};