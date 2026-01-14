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
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('display_gacha_caution_id', 255)
                ->nullable()
                ->after('display_information_id')
                ->comment('ガシャ注意事項のid（adm_gacha_cautions.id）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->dropColumn('display_gacha_caution_id');
        });
    }
};
