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
            $table->string('id')->primary();
            $table->string('group_id')->nullable(false)->comment('グループ');
            $table->string('rule_type')->nullable(false)->comment('ルール条件タイプ');
            $table->string('rule_value')->nullable()->comment('ルール条件値');
            $table->bigInteger('release_key')->nullable(false)->default(1);
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
