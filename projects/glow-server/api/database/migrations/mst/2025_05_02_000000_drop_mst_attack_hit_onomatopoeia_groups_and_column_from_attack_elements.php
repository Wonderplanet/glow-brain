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
        // mst_attack_hit_onomatopoeia_groupsテーブルを削除
        Schema::dropIfExists('mst_attack_hit_onomatopoeia_groups');

        // mst_attack_elementsテーブルからhit_onomatopoeia_group_idカラムを削除
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            if (Schema::hasColumn('mst_attack_elements', 'hit_onomatopoeia_group_id')) {
                $table->dropColumn('hit_onomatopoeia_group_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst_attack_hit_onomatopoeia_groupsテーブルを再作成
        Schema::create('mst_attack_hit_onomatopoeia_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('asset_key1')->default('');
            $table->string('asset_key2')->default('');
            $table->string('asset_key3')->default('');
        });

        // mst_attack_elementsテーブルにhit_onomatopoeia_group_idカラムを追加
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->string('hit_onomatopoeia_group_id')->after('hit_parameter2');
        });
    }
};
