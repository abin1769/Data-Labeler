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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();
            $table->integer('label')->nullable(); // 0, 1, 2
            $table->string('labeled_by')->nullable();
            $table->string('prodi')->nullable();
            $table->string('label_status')->default('unlabeled'); // 'unlabeled', 'pending', 'approved'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
