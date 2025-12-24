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
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_pemasukan', 15, 2)->default(0);
            $table->decimal('total_pengeluaran', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0); // surplus/defisit
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->json('keterangan_libur')->nullable(); // Holiday info from Dayoff API
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
