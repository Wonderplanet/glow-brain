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
        Schema::create('mst_mission_event_dependencies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('group_id', 255)->comment('依存関係のグルーピングID');
            $table->string('mst_mission_event_id', 255)->comment('mst_mission_events.id');
            $table->integer('unlock_order')->unsigned()->comment('グループ内でのミッションの開放順');
            $table->unique(['group_id', 'mst_mission_event_id'], 'group_id_mst_mission_event_id_unique');
            $table->unique(['group_id', 'unlock_order'], 'group_id_unlock_order_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_mission_event_dependencies');
    }
};
