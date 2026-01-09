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
	    // mst_packsのcost_typeのEnumの中身を追加
        Schema::table('mst_packs', function (Blueprint $table) {
            $table->enum('cost_type', ['Cash','Diamond','PaidDiamond','Ad','Free'])
                ->comment('販売コスト種別')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('mst_packs', function (Blueprint $table) {
            $table->enum('cost_type', ['Cash','PaidDiamond'])
                ->comment('販売コスト種別')
                ->change();
        });
    }
};
