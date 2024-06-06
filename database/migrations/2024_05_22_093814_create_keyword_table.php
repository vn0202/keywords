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
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->index();
            $table->integer("file_id")->index()->nullable();
            $table->string('slug')->nullable();
            $table->string('type')->nullable();


            $table->string('country')->nullable()->default('vn');
            $table->string('source')->nullable()->default('Ahref');
            $table->integer('status_search')->default(0);
            $table->integer('duplicate_id')->default(0);

            $table->json('raw')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keyword');
    }
};
