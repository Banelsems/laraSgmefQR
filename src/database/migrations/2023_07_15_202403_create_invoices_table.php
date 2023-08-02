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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->unique()->constrained('sales')->onUpdate('cascade')->onDelete('cascade');
            $table->json('invoiceRequestDataDto')->nullable();
            $table->json('invoiceResponseDataDto')->nullable();
            /** ENUM */
            $table->string('statusInvoice')->default('create');
            /** end ENUM */
            $table->json('securityElementsDto')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
