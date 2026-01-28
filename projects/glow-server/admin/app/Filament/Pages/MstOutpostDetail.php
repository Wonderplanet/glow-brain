<?php

namespace App\Filament\Pages;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Models\Mst\MstOutpost;
use Filament\Pages\Page;

class MstOutpostDetail extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.mst-outpost-detail';

    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $title = 'ゲート';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::GATE_DISPLAY_ORDER->value; // メニューの並び順

    public MstOutpost $outpost;

    public function mount(): void
    {
        // ゲート入れ替え実装後の将来的にはGETパラメータでoutpost_idが渡される想定
        $this->outpost = MstOutpost::query()
            ->with([
                'mst_outpost_enhancement',
                'mst_outpost_enhancement.mst_outpost_enhancement_i18n',
                'mst_outpost_enhancement.mst_outpost_enhancement_level' => function ($query) {
                    $query->orderBy('level', 'asc');
                },
                'mst_outpost_enhancement.mst_outpost_enhancement_level.mst_outpost_enhancement_level_i18n'
            ])
            ->first();
    }
}
