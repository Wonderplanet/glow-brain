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
        Schema::table('mst_units', function (Blueprint $table) {
            // 列追加
            //   - name: specialAttackInitialCoolTime (after: summonCoolTime)
            //     type: int
            $table->unsignedInteger('special_attack_initial_cool_time')->nullable(false)->after('summon_cool_time');
        });

        Schema::table('mst_units', function (Blueprint $table) {
            // 列追加
            //   - name: specialAttackCoolTime
            //     type: int
            $table->unsignedInteger('special_attack_cool_time')->nullable(false)->after('special_attack_initial_cool_time');
        });

        Schema::table('mst_units', function (Blueprint $table) {
            // 列削除
            //   - name: attackComboCycle
            $table->dropColumn('attack_combo_cycle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            // 列削除
            //   - name: specialAttackInitialCoolTime
            //   - name: specialAttackCoolTime
            $table->dropColumn('special_attack_initial_cool_time');
            $table->dropColumn('special_attack_cool_time');

            // attackComboCycle追加
            $table->unsignedInteger('attack_combo_cycle')->nullable(false)->after('max_attack_power');
        });
    }
};
