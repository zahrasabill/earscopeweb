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
        Schema::table('penanganans', function (Blueprint $table) {
            $table->enum('status', ['unassigned', 'assigned'])->default('unassigned')->after('created_by');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->after('status');
        
            $table->index('assigned_to');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penanganans', function (Blueprint $table) {
            //
        });
    }
};
