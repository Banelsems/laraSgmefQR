<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration additive : ajoute le statut 'expired' à la colonne status.
 *
 * Une facture pending dont expires_at est dépassé passe désormais au statut
 * EXPIRED (distinct d'ERROR qui indique une erreur technique ou un rejet DGI).
 *
 * Expand-and-contract : cette migration ne supprime aucun statut existant.
 *
 * MySQL/MariaDB : ALTER COLUMN ENUM pour ajouter 'expired'.
 * SQLite : recréation de la table (SQLite ne supporte pas ALTER COLUMN
 * sur une constraint CHECK). Les données existantes sont préservées.
 */
return new class () extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->migrateSqlite();
            return;
        }

        // MySQL / MariaDB : modifier la colonne ENUM
        $table = $this->getTableName();

        DB::statement(
            "ALTER TABLE `{$table}` "
            . "MODIFY COLUMN `status` ENUM('pending','confirmed','cancelled','expired','error') "
            . "NOT NULL DEFAULT 'pending' "
            . "COMMENT 'Statut de la facture'"
        );
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Avant de retirer le statut, migrer les expired vers error
        $table = $this->getTableName();

        DB::table($table)
            ->where('status', 'expired')
            ->update(['status' => 'error']);

        if ($driver === 'sqlite') {
            $this->rollbackSqlite();
            return;
        }

        DB::statement(
            "ALTER TABLE `{$table}` "
            . "MODIFY COLUMN `status` ENUM('pending','confirmed','cancelled','error') "
            . "NOT NULL DEFAULT 'pending' "
            . "COMMENT 'Statut de la facture'"
        );
    }

    private function getTableName(): string
    {
        $connection = Schema::getConnection();
        $prefix = config('database.connections.' . $connection->getName() . '.prefix', '');

        return $prefix . 'invoices';
    }

    /**
     * SQLite : recréer la table avec la nouvelle contrainte CHECK.
     * Préserve toutes les données existantes.
     */
    private function migrateSqlite(): void
    {
        // SQLite ne supporte pas ALTER COLUMN sur une constraint CHECK.
        // On crée manuellement la table avec le nouveau schéma.
        Schema::create('invoices_new', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->nullable()->unique();
            $table->string('ifu', 13);
            $table->string('customer_ifu', 13)->nullable();
            $table->enum('type', ['FV', 'FA', 'EV', 'EA']);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired', 'error'])->default('pending');
            $table->string('reference')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('security_elements')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->string('mecf_code')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['ifu', 'created_at']);
            $table->index('customer_ifu');
            $table->index('reference');
            $table->index('expires_at');
            $table->index('confirmed_at');
        });

        // Copier les données existantes
        DB::statement('INSERT INTO invoices_new SELECT * FROM invoices');

        // Remplacer l'ancienne table
        Schema::drop('invoices');
        Schema::rename('invoices_new', 'invoices');
    }

    private function rollbackSqlite(): void
    {
        Schema::create('invoices_old', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->nullable()->unique();
            $table->string('ifu', 13);
            $table->string('customer_ifu', 13)->nullable();
            $table->enum('type', ['FV', 'FA', 'EV', 'EA']);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'error'])->default('pending');
            $table->string('reference')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('security_elements')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->string('mecf_code')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['ifu', 'created_at']);
            $table->index('customer_ifu');
            $table->index('reference');
            $table->index('expires_at');
            $table->index('confirmed_at');
        });

        DB::statement('INSERT INTO invoices_old SELECT * FROM invoices');
        Schema::drop('invoices');
        Schema::rename('invoices_old', 'invoices');
    }
};
