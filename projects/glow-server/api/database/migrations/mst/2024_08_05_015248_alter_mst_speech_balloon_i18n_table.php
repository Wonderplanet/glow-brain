<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_speech_balloons_i18n` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
    //     `balloon_type` enum('Maru','Fuwa','Toge') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容

    // 列追加: conditionType, side, duration
    // - name: language
    // type: Language
    // - name: conditionType
    // type: SpeechBalloonConditionType
    // - name: balloonType
    // type: SpeechBalloonType
    // - name: side
    // type: SpeechBalloonSide
    // - name: duration
    // type: float

    // 列の型情報
    //     - name: SpeechBalloonSide
    //     params:
    //       - name: Right
    //         number: 0
    //       - name: Left
    //         number: 1

    //   - name: SpeechBalloonConditionType
    //     params:
    //       - name: Summon
    //       - name: SpecialAttackCharge
    //       - name: SpecialAttack


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_speech_balloons_i18n', function (Blueprint $table) {
            $table->enum('condition_type', ['Summon', 'SpecialAttackCharge', 'SpecialAttack'])->after('language');
            $table->enum('side', ['Right', 'Left'])->after('balloon_type');
            $table->float('duration')->after('side');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_speech_balloons_i18n', function (Blueprint $table) {
            $table->dropColumn('condition_type');
            $table->dropColumn('side');
            $table->dropColumn('duration');
        });
    }
};
