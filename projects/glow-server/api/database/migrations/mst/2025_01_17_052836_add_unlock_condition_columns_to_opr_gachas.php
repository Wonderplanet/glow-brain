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
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table
                ->enum('unlock_condition_type', ['MainPartTutorialComplete'])
                ->nullable()
                ->default(null)
                ->after('appearance_condition')
                ->comment('開放条件タイプ');
            $table
                ->unsignedSmallInteger('unlock_duration_hours')
                ->nullable()
                ->default(null)
                ->after('unlock_condition_type')
                ->comment('条件達成からの開放時間');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->dropColumn('unlock_condition_type');
            $table->dropColumn('unlock_duration_hours');
        });
    }
};
