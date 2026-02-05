<?php

declare(strict_types=1);

namespace App\Constants;

// TODO api側にクラスが作成されたらそちらを参照する
enum PvpRewardCategory: string
{
    case RANKING = "Ranking";
    case RANK_CLASS = 'RankClass';
}
