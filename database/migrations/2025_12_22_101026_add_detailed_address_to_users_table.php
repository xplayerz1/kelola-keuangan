<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom alamat lengkap dari API Wilayah Indonesia
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom domisili_api yang lama (akan diganti dengan struktur baru)
            $table->dropColumn('domisili_api');
            
            // Tambah kolom alamat hierarkis dari API
            $table->string('provinsi_id')->nullable()->after('email');
            $table->string('provinsi_nama')->nullable()->after('provinsi_id');
            $table->string('kabkota_id')->nullable()->after('provinsi_nama');
            $table->string('kabkota_nama')->nullable()->after('kabkota_id');
            $table->string('kecamatan_id')->nullable()->after('kabkota_nama');
            $table->string('kecamatan_nama')->nullable()->after('kecamatan_id');
            $table->string('kelurahan_id')->nullable()->after('kecamatan_nama');
            $table->string('kelurahan_nama')->nullable()->after('kelurahan_id');
            $table->string('kode_pos')->nullable()->after('kelurahan_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan ke struktur lama
            $table->dropColumn([
                'provinsi_id', 'provinsi_nama',
                'kabkota_id', 'kabkota_nama',
                'kecamatan_id', 'kecamatan_nama',
                'kelurahan_id', 'kelurahan_nama',
                'kode_pos'
            ]);
            
            $table->string('domisili_api')->nullable()->after('email');
        });
    }
};
