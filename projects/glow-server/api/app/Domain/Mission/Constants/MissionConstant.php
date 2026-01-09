<?php

declare(strict_types=1);

namespace App\Domain\Mission\Constants;

use App\Domain\Mission\Enums\MissionCriterionType;

class MissionConstant
{
    public const COMPOSITE_MISSION_CRITERION_TYPES = [
        MissionCriterionType::MISSION_CLEAR_COUNT->value,
        MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value,
    ];

    /**
     * @var string criterion_keyを構成するcriterion_typeとcriterion_valueの区切り文字
     * 例：criterion_key = 「SpecificQuestClear:1」の場合
     * criterion_type = SpecificQuestClear
     * criterion_value = 1（クエストID=1）
     */
    public const CRITERION_KEY_DELIMITER = ':';

    /**
     * @var string ミッション条件指定値(criterion_value)の区切り文字
     *
     * 例：criterion_type = SpecificUnitStageClearCountの場合
     * criterion_value = <mst_units.id>.<mst_stages.id>（2つのID文字列を連結した文字列）
     * ユニット1でステージ1をクリアしようの場合、criterion_value = unit1.stage1 という文字列を指定する
     */
    public const CRITERION_VALUE_DELIMITER = '.';

    /* @var int デイリーボーナスの最大連続ログイン日数 */
    public const MAX_DAILY_BONUS_LOGIN_CONTINUE_DAY_COUNT = 7;

    /** @var int ミッション進捗値の初期値 */
    public const PROGRESS_INITIAL_VALUE = 0;

    /** @var int 初心者ミッションの挑戦できるデータが段階的に開放され増えていく最大日数 */
    public const MAX_BEGINNER_UNLOCK_DAY_COUNT = 7;
}
