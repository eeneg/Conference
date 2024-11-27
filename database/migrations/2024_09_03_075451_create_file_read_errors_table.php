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
            $table->uuid('file_id');
            $table->foreign('file_id')
                ->references('id')
                ->on('files')
                ->onDelete('cascade');
            $table->text('verbose');
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
