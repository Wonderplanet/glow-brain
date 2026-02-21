<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     *
     * gacha_typeのenum値 'StepUp' を 'Stepup' に統一する
     */
    public function up(): void
    {
        $enum = "'Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal','Tutorial','Stepup'";
        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN gacha_type ENUM({$enum}) NULL COMMENT 'ガシャのタイプ'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enum = "'Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal','Tutorial','StepUp'";
        DB::statement("ALTER TABLE opr_gachas MODIFY COLUMN gacha_type ENUM({$enum}) NULL COMMENT 'ガシャのタイプ'");
    }
};
