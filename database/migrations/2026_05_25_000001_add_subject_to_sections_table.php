<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sections') && !Schema::hasColumn('sections', 'subject')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->string('subject')->nullable()->after('teacher_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sections', 'subject')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropColumn('subject');
            });
        }
    }
};
