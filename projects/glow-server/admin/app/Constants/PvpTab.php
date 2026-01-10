<?php

namespace App\Constants;

enum PvpTab: string
{
    case PVP_LIST = 'ランクマッチ';
    case PVP_RANK = 'ランク帯';
    case PVP_BONUS_POINT = 'ボーナスポイント';
    case PVP_DUMMY = 'ダミープレイヤー';
}
