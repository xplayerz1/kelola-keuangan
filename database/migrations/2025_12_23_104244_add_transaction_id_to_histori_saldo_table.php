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
        Schema::table('histori_saldo', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->after('id_saldo')->constrained('transactions')->onDelete('set null');
            $table->string('keterangan')->nullable()->after('saldo_sesudah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('histori_saldo', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn(['transaction_id', 'keterangan']);
        });
    }
};
