<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usr_mission_normals', function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('ID');
            $table->string('usr_user_id', 255)->nullable(false)->comment('usr_users.id');
            $table->unsignedTinyInteger('mission_type')->nullable(false)->comment('ミッションタイプのenum値');
            $table->string('mst_mission_id', 255)->nullable(false)->comment('ミッションのマスタデータのID(mst_mission_xxxs.id)');
            $table->unsignedTinyInteger('status')->nullable(false)->comment('ミッションステータス');
            $table->unsignedBigInteger('progress')->nullable(false)->default(0)->comment('進捗値');
            $table->timestampTz('next_reset_at')->nullable()->comment('次回リセットする日時');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mission_type', 'mst_mission_id'], 'pk_user_mission_type_mission_id');
            $table->index(['usr_user_id', 'status'], 'idx_user_id_status');

            $table->comment('ノーマル系ミッションのユーザー進捗管理');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_mission_normals');
    }
};
