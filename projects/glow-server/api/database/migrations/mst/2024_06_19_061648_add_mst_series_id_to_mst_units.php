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
        Schema::table('mst_units', function (Blueprint $table) {
            $table->string('mst_series_id')->default('')->after('image_id')->comment('作品ID');
        });
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->string('mst_series_id')->default('')->after('color')->comment('作品ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('mst_series_id');
        });
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropColumn('mst_series_id');
        });
    }
};
