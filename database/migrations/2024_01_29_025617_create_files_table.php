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
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->uuid('storage_id');
            $table->foreign('storage_id')
                ->references('id')
                ->on('storages');
            $table->string('title');
            $table->string('file_name');
            $table->date('date');
            $table->string('hash_name');
            $table->string('details');
            $table->boolean('for_review')->default(false);
            $table->boolean('latest')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
