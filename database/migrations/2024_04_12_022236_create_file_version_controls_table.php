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
        Schema::create('file_version_controls', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->uuid('control_id')->unique();
            $table->uuid('file_id')->unique();
            $table->integer('version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_version_controls');
    }
};
