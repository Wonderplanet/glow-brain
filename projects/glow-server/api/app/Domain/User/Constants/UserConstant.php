<?php

declare(strict_types=1);

namespace App\Domain\User\Constants;

class UserConstant
{
    public const PLATFORM_IOS = 1;
    public const PLATFORM_ANDROID = 2;

    public const PLATFORM_STRING_LIST = [
        self::PLATFORM_IOS => 'iOS',
        self::PLATFORM_ANDROID => 'Android',
    ];

    public const REQUIRED_CHANGE_NAME_DAIAMOND = 100;
    public const MAX_STR_COUNT = 18;

    /** @var int コインのシステム上限 */
    public const MAX_COIN_COUNT = 99999999;

    /** @var int スタミナのシステム上限 */
    public const MAX_STAMINA = 999;

    /** @var int 時間回復で増える最大量 */
    public const MAX_STAMINA_RECOVERY = 99;

    /** @var int 1回復するのにかかる時間(分) */
    public const RECOVERY_STAMINA_MINUTE = 3;

    /** @var int 1分あたりの回復量 */
    public const RECOVERY_STAMINA_PER_MINUTE = 1;

    /** @var int 広告でスタミナ購入する際の広告視聴インターバル(分) */
    public const DAILY_BUY_STAMINA_AD_INTERVAL_MINUTES = 60;

    /** @var int 1日の内に広告でスタミナ購入できる最大購入回数 */
    public const MAX_DAILY_BUY_STAMINA_AD_COUNT = 3;

    /** @var int 広告でスタミナ購入したときの最大スタミナにおける回復パーセンテージ */
    public const BUY_STAMINA_AD_PERCENTAGE_OF_MAX_STAMINA = 50;

    /** @var int ダイヤモンドでスタミナ購入したときの最大スタミナにおける回復パーセンテージ */
    public const BUY_STAMINA_DIAMOND_PERCENTAGE_OF_MAX_STAMINA = 100;

    /** @var int スタミナ購入に必要なダイヤモンド */
    public const BUY_STAMINA_DIAMOND_AMOUNT = 100;

    public const REGION_MY_ID_PREFIX = [
        'JP' => 'A',
    ];

    public const MAX_USER_NAME_LENGTH = 10;

    public const USER_NAME_CHANGE_INTERVAL_HOURS = 24;

    /**
     * ユーザーの年齢のデフォルト値
     */
    public const AGE_DEFAULT = 0;

    /** @var int 登録できない年齢の最小値 */
    public const UNREGISTERABLE_AGE_MIN = 150;
}
