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
        Schema::create('opr_stage_continue_ad_schedules', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('max_ad_continue')->unsigned()->default(1)->comment('コンティニュー可能回数');
            $table->timestampTz('start_at')->comment('開始日時');
            $table->timestampTz('end_at')->comment('終了日時');
            $table->bigInteger('release_key')->default(1)->comment('リリースキー');

            //table comment
            $table->comment('広告でのコンティニュー可能回数スケジュール情報');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_stage_continue_ad_schedules');
    }
};
