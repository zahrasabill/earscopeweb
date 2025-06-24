<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penanganans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->enum('status', ['unassigned', 'assigned'])->default('unassigned');
            $table->date('tanggal_penanganan');
            $table->text('keluhan')->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->text('diagnosis_manual')->nullable();
            $table->enum('telinga_terkena', ['kiri', 'kanan', 'keduanya']);
            $table->text('tindakan')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index([
                'user_id',
                'tanggal_penanganan'
            ]);

            // relation to user
            $table->foreign('user_id', 'fk_penanganan_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('created_by', 'fk_penanganan_created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanganans');
    }
};
