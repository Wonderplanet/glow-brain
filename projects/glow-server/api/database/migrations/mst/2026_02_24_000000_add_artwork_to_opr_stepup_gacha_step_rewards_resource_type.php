<?php

declare(strict_types=1);

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     *
     * opr_stepup_gacha_step_rewards の resource_type enum に 'Artwork' を追加する
     */
    public function up(): void
    {
        $enum = "'Exp','Coin','FreeDiamond','Item','Emblem','Stamina','Unit','Artwork'";
        DB::statement("ALTER TABLE opr_stepup_gacha_step_rewards MODIFY COLUMN resource_type ENUM({$enum}) NOT NULL COMMENT '報酬リソースタイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enum = "'Exp','Coin','FreeDiamond','Item','Emblem','Stamina','Unit'";
        DB::statement("ALTER TABLE opr_stepup_gacha_step_rewards MODIFY COLUMN resource_type ENUM({$enum}) NOT NULL COMMENT '報酬リソースタイプ'");
    }
};
