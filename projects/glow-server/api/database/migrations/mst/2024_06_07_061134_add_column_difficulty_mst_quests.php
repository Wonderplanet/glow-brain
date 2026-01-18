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
        Schema::table('mst_quests', function (Blueprint $table) {
            $table->string('quest_group')->nullable(true)->default(null)->after('release_key')->comment('同クエストとして表示をまとめるグループ');
            $table->enum('difficulty', ['Normal', 'Hard', 'VeryHard'])->default('Normal')->after('quest_group')->comment('難易度');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_quests', function (Blueprint $table) {
            $table->dropColumn('quest_group');
            $table->dropColumn('difficulty');
        });
    }
};
