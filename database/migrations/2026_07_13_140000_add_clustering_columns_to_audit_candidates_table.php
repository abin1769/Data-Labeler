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
        Schema::table('audit_candidates', function (Blueprint $table) {
            $table->string('sub_cluster_id')->nullable()->after('label_quality_score');
            $table->float('neighbor_conflict_rate')->nullable()->after('sub_cluster_id');
            $table->string('dominant_neighbor_class')->nullable()->after('neighbor_conflict_rate');
            $table->float('hdbscan_outlier_score')->nullable()->after('dominant_neighbor_class');
            $table->float('priority_score')->nullable()->after('hdbscan_outlier_score');

            $table->index('sub_cluster_id');
            $table->index('priority_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_candidates', function (Blueprint $table) {
            $table->dropColumn([
                'sub_cluster_id',
                'neighbor_conflict_rate',
                'dominant_neighbor_class',
                'hdbscan_outlier_score',
                'priority_score'
            ]);
        });
    }
};
