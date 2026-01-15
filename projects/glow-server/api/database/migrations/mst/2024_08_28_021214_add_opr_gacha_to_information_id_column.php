<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = \App\Domain\Constants\Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->string('display_information_id', 255)->comment('ガチャ詳細用お知らせID')->after('display_mst_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_gachas', function (Blueprint $table) {
            $table->dropColumn('display_information_id');
        });
    }
};
