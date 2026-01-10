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
        Schema::table('mst_mission_beginners_i18n', function (Blueprint $table) {
            $table->dropColumn('mst_mission_achievement_id');
            $table->string('mst_mission_beginner_id', 255)->after('id')->comment('mst_mission_beginners.id');
            $table->bigInteger('release_key')->default(1)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_mission_beginners_i18n', function (Blueprint $table) {
            $table->dropColumn('mst_mission_beginner_id');
            $table->string('mst_mission_achievement_id', 255)->after('id')->comment('mst_mission_achievements.id');
            $table->dropColumn('release_key');
        });
    }
};
