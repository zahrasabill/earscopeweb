<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('penanganans', function (Blueprint $table) {
            $table->dropColumn(['is_sent_to_patient', 'sent_at', 'catatan_pengiriman', 'pdf_url']);
        });
    }

    public function down()
    {
        Schema::table('penanganans', function (Blueprint $table) {
            $table->boolean('is_sent_to_patient')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text('catatan_pengiriman')->nullable();
            $table->string('pdf_url')->nullable();
        });
    }
};
