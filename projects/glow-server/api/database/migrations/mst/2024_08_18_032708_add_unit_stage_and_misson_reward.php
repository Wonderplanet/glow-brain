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
        DB::statement("ALTER TABLE mst_stage_reward_groups MODIFY COLUMN resource_type ENUM('Exp','Coin','FreeDiamond','Item','Emblem', 'Unit') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ'");
        DB::statement("ALTER TABLE mst_mission_rewards MODIFY COLUMN resource_type ENUM('Exp','Coin','FreeDiamond','Item','Emblem', 'Unit') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_stage_reward_groups MODIFY COLUMN resource_type ENUM('Exp','Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ'");
        DB::statement("ALTER TABLE mst_mission_rewards MODIFY COLUMN resource_type ENUM('Exp','Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ'");
    }
};
