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
        Schema::create('import_files', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();

            $table->string('namespace')->nullable()->default(null);
            $table->string('source')->nullable()->default('Ahref');
            $table->string('country')->nullable()->default('vn');
            $table->string('path')->nullable();
            $table->json('meta')->nullable();
            $table->integer('status')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_files');
    }
};
