<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->date('purchase_date');
            $table->string('invoice_number')->nullable();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('supplier_id');
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};