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
        Schema::create('references', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->uuid('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories');
            $table->string('title')->unique();
            $table->date('date');
            $table->string('details');
            $table->string('file_name');
            $table->string('hash_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
