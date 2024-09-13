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
        Schema::create('file_read_errors', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('file_id')->onDelete('cascade');
            $table->string('verbose');
            $table->string('remark');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_read_errors');
    }
};
