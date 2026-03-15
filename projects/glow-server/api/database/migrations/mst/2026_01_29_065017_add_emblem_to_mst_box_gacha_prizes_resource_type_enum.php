<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE mst_box_gacha_prizes MODIFY COLUMN resource_type ENUM('Item','Artwork','FreeDiamond','Coin','Unit','Emblem') COMMENT '報酬タイプ'");
    }

    /**
     * Reverse the migrations.
     *
     * 注意: resource_type='Emblem'を持つレコードが存在する場合はエラーになる。
     * その場合は手動でデータ対応を行うこと。
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mst_box_gacha_prizes MODIFY COLUMN resource_type ENUM('Item','Artwork','FreeDiamond','Coin','Unit') COMMENT '報酬タイプ'");
    }
};
