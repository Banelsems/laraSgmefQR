<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Identifiants
            $table->string('uid')->nullable()->unique()->comment('UID retourné par l\'API SyGM-eMCF');
            $table->string('ifu', 13)->comment('IFU de l\'émetteur');
            $table->string('customer_ifu', 13)->nullable()->comment('IFU du client');
            
            // Type et statut
            $table->enum('type', ['FV', 'FA', 'EV', 'EA'])->comment('Type de facture');
            $table->enum('status', [
                InvoiceStatusEnum::PENDING->value,
                InvoiceStatusEnum::CONFIRMED->value,
                InvoiceStatusEnum::CANCELLED->value,
                InvoiceStatusEnum::ERROR->value,
            ])->default(InvoiceStatusEnum::PENDING->value)->comment('Statut de la facture');
            
            // Montants
            $table->decimal('total_amount', 15, 2)->comment('Montant total de la facture');
            
            // Données brutes pour le tracking
            $table->json('raw_request')->nullable()->comment('Données brutes de la requête envoyée à l\'API');
            $table->json('raw_response')->nullable()->comment('Données brutes de la réponse de l\'API');
            
            // Éléments de sécurité
            $table->text('qr_code_data')->nullable()->comment('Données du QR code');
            $table->string('mecf_code')->nullable()->comment('Code MECeF/DGI');
            
            // Timestamps spécialisés
            $table->timestamp('confirmed_at')->nullable()->comment('Date de confirmation de la facture');
            $table->timestamp('cancelled_at')->nullable()->comment('Date d\'annulation de la facture');
            
            // Timestamps standards
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour les performances
            $table->index(['status', 'created_at']);
            $table->index(['ifu', 'created_at']);
            $table->index('customer_ifu');
            $table->index('confirmed_at');
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
