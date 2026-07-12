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
        Schema::create('audit_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();

            // Kelas disimpan sebagai string ("0_Recyclable", dst), bukan int seperti
            // Image::label -- supaya cocok persis dengan kolom CSV dari pipeline audit
            // Python (label_issues.csv / label_review.csv / relabel_review.csv), tanpa
            // perlu mapping bolak-balik saat import/export.
            $table->string('given_label');
            $table->string('predicted_label')->nullable();
            $table->float('label_quality_score')->nullable();

            // Putaran 1: A (salah label) / B (kontaminasi) / C (ambigu) / D (model error).
            $table->string('round1_decision')->nullable();
            $table->string('round1_note')->nullable();
            $table->string('round1_by')->nullable();
            $table->timestamp('round1_at')->nullable();

            // Putaran 2 (cuma diisi kalau round1_decision == 'A'): kelas tujuan yang benar,
            // atau "CONTAMINATION" kalau ternyata bukan salah label tapi kontaminasi.
            $table->string('round2_decision')->nullable();
            $table->string('round2_by')->nullable();
            $table->timestamp('round2_at')->nullable();

            $table->timestamps();

            $table->index('round1_decision');
            $table->index('round2_decision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_candidates');
    }
};
