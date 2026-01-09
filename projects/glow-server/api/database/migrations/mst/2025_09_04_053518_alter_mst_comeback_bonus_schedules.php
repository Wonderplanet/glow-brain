<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_comeback_bonus_schedules', function (Blueprint $table) {
            $table->integer('duration_days')->comment('有効日数')->after('inactive_condition_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_comeback_bonus_schedules', function (Blueprint $table) {
            $table->dropColumn('duration_days');
        });
    }
};
