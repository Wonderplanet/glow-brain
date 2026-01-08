<?php

namespace App\Constants;

use App\Domain\Pvp\Constants\PvpConstant as ApiPvpConstant;

class PvpConstant
{
    /**
     * mst_pvpsにあるデフォルト設定レコードのID
     */
    public const DEFAULT_MST_PVP_ID = ApiPvpConstant::DEFAULT_MST_PVP_ID;

    /**
     * ランクマッチマスター参照のタブグループ情報
     */
    public const array TAB_GROUPS = [
        [
            '' => [
                'ランクマッチ' => 'App\Filament\Pages\MstPvps',
                'ランク帯' => 'App\Filament\Pages\MstPvpRanks',
                'ボーナスポイント' => 'App\Filament\Pages\MstPvpBonusPoints',
                'ダミープレイヤー' => 'App\Filament\Pages\MstPvpDummies',
            ],
        ],
    ];
}
