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
        // difficultカラムをNOT NULLに変更
        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN difficulty ENUM('Normal', 'Hard', 'Extra') NOT NULL COMMENT '難易度'");
        
        // target_id_typeカラムをNOT NULLに変更
        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN target_id_type ENUM('Quest', 'Series') NOT NULL COMMENT '指定するIDのタイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // difficultカラムを再度NULLABLEに戻す
        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN difficulty ENUM('Normal', 'Hard', 'Extra') COMMENT '難易度'");
        
        // target_id_typeカラムを再度NULLABLEに戻す
        DB::statement("ALTER TABLE opr_campaigns MODIFY COLUMN target_id_type ENUM('Quest', 'Series') COMMENT '指定するIDのタイプ'");
    }
};
