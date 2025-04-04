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
        Schema::create('hasil_pemeriksaan', function (Blueprint $table) {
            $table->id();$table->foreignId('pasien_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('video_raw_path');
            $table->string('video_ai_path');
            $table->text('diagnosa');
            $table->timestamp('examined_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_pemeriksaan');
    }
};
