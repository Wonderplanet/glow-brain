<?php

declare(strict_types=1);

namespace App\Domain\Unit\Constants;

class UnitConstant
{
    public const MST_HERO_UNIT_TYPE = 'Hero';

    public const MST_MONSTER_UNIT_TYPE = 'Monster';

    /** @var int ユニットの初期レベル */
    public const FIRST_UNIT_LEVEL = 1;

    /** @var int ユニットの初期ランク */
    public const FIRST_UNIT_RANK = 0;

    /** @var int ユニットの初期グレードレベル */
    public const FIRST_UNIT_GRADE_LEVEL = 1;

    /** @var float ユニットステータスのデフォルト指数 */
    public const DEFAULT_UNIT_STATUS_EXPONENT = 1.0;

    /** @var int ユニットのランクに応じたデフォルト係数 */
    public const DEFAULT_UNIT_RANK_COEFFICIENT = 0;

    /** @var int ユニットのグレードレベルに応じたデフォルト係数 */
    public const DEFAULT_UNIT_GRADE_COEFFICIENT = 0;
}
