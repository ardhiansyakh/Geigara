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
        Schema::create('radiation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('Aman');       // Aman, Sedang, Berbahaya
            $table->string('gas', 20)->default('Aman');           // Status gas (sama dgn status)
            $table->integer('raw')->default(0);                   // Nilai raw ADC sensor MQ-2 (0-4095)
            $table->double('radiation', 8, 4)->nullable();        // Backward compat (diisi = raw)
            $table->integer('cpm')->nullable();                   // Backward compat
            $table->double('temperature', 6, 2)->nullable();      // Suhu °C dari DHT22
            $table->double('humidity', 6, 2)->nullable();         // Kelembapan % dari DHT22
            $table->string('device_id', 50)->default('ESP32');
            $table->timestamps();

            // Index untuk pencarian realtime cepat
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiation_logs');
    }
};
