<?php

declare(strict_types=1);

namespace App\Domain\Stage\Constants;

class StageConstant
{
    /*
     * ステージへ1回挑戦するごとに消費されるスタミナ
     */
    public const STAMINA_COST = 5;

    /**
     * コンティニューに必要なダイヤの消費数(mst_configに定義がない場合)
     */
    public const CONTINUE_DIAMOND_COST = 100;

    /**
     * コンティニューの最大回数
     */
    public const CONTINUE_MAX_COUNT = 1;
}
