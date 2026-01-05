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
        // TiDBの仕様では、ALTER TABLE実行前のカラムのみ参照できるため、追加するカラムにafterを使用する場合はクエリを分割する
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->string('opr_message_id', 255)->nullable()->change();
            $table->text('title')->after('create_status');
        });
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->timestampTz('start_at')->after('title');
        });
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->timestampTz('expired_at')->nullable()->after('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            $table->dropColumn('expired_at');
            $table->dropColumn('start_at');
            $table->dropColumn('title');
            $table->string('opr_message_id', 255)->nullable(false)->change();
        });
    }
};
