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
        Schema::create('mst_stage_event_rules', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('group_id', 255)->comment('グループ');
            $table->string('rule_type', 255)->comment('ルール条件タイプ');
            $table->string('rule_value', 255)->nullable()->comment('ルール条件値');
            $table->bigInteger('release_key')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mst_stage_event_rules');
    }
};
