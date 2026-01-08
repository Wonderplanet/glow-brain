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
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->string('fixed_prize_description')->default('')->comment('確定枠の表示文言')->after('pickup_upper_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->dropColumn('fixed_prize_description');
        });
    }
};
