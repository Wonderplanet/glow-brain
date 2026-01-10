<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // unlock_condition_typeのenumでnullを扱いたくないとのことでNoneを追加する
        // すでにunlock_condition_typeにnullが入っているので以下の手順で対応する
        // 1. enumにNoneを追加
        // 2. nullをNoneに更新
        // 3. enumをnot nullに変更
        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN unlock_condition_type ENUM('None', 'MainPartTutorialComplete') default 'None' COMMENT '開放条件タイプ'");

        DB::table('opr_gachas')
            ->whereNull('unlock_condition_type')
            ->update(['unlock_condition_type' => 'None']);

        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN unlock_condition_type ENUM('None', 'MainPartTutorialComplete') not null default 'None' COMMENT '開放条件タイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN unlock_condition_type ENUM('None', 'MainPartTutorialComplete') default null COMMENT '開放条件タイプ'");

        DB::table('opr_gachas')
            ->where('unlock_condition_type', 'None')
            ->update(['unlock_condition_type' => null]);

        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN unlock_condition_type ENUM('MainPartTutorialComplete') default null COMMENT '開放条件タイプ'");
    }
};
