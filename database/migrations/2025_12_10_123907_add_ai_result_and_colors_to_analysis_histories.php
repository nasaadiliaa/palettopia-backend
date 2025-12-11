<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('analysis_histories', 'colors')) {
                $table->json('colors')->nullable()->after('input_data');
            }
            if (!Schema::hasColumn('analysis_histories', 'ai_result')) {
                $table->json('ai_result')->nullable()->after('colors');
            }
        });
    }

    public function down(): void
    {
        Schema::table('analysis_histories', function (Blueprint $table) {
            if (Schema::hasColumn('analysis_histories', 'ai_result')) {
                $table->dropColumn('ai_result');
            }
            if (Schema::hasColumn('analysis_histories', 'colors')) {
                $table->dropColumn('colors');
            }
        });
    }
};
