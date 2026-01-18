<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $query =<<<EOF
ALTER TABLE opr_gachas 
MODIFY COLUMN start_at timestamp NOT NULL COMMENT '開始日時', 
MODIFY COLUMN end_at timestamp NOT NULL COMMENT '終了日時';
EOF;
        DB::statement($query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $query =<<<EOF
ALTER TABLE opr_gachas 
MODIFY COLUMN start_at timestamp NULL DEFAULT NULL COMMENT '開催期間', 
MODIFY COLUMN end_at timestamp NULL DEFAULT NULL COMMENT '開催期間';
EOF;
        DB::statement($query);
    }
};
