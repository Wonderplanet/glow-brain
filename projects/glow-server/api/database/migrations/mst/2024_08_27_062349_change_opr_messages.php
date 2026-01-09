<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = \App\Domain\Constants\Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // opr_messagesのaccount_created_start_atをnullableに変更するSQL
        DB::statement('ALTER TABLE opr_messages MODIFY account_created_start_at TIMESTAMP NULL COMMENT "全体配布条件とするアカウント作成日時(開始)"');
        DB::statement('ALTER TABLE opr_messages MODIFY account_created_end_at TIMESTAMP NULL COMMENT "全体配布条件とするアカウント作成日時(終了)"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE opr_messages MODIFY account_created_start_at TIMESTAMP COMMENT "全体配布条件とするアカウント作成日時(開始)"');
        DB::statement('ALTER TABLE opr_messages MODIFY account_created_end_at TIMESTAMP COMMENT "全体配布条件とするアカウント作成日時(終了)"');
    }
};
