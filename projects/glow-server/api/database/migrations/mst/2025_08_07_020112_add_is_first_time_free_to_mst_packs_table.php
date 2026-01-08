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
        Schema::table('mst_packs', function (Blueprint $table) {
            $table->tinyInteger('is_first_time_free')->default(0)->after('is_recommend')->comment('初回無料フラグ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_packs', function (Blueprint $table) {
            $table->dropColumn('is_first_time_free');
        });
    }
};
