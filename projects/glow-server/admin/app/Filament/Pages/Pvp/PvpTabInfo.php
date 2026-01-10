<?php

namespace App\Filament\Pages\Pvp;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\PvpConstant;
use App\Constants\PvpTab;
use App\Filament\Authorizable;
use Filament\Pages\Page;
use Livewire\WithPagination;

class PvpTabInfo extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;

    protected static string $view = 'filament.pages.pvp-tab-info';

    protected static ?string $title = 'ランクマッチ';

    public string $currentTab = '';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::PVP_DISPLAY_ORDER->value; // メニューの並び順

    public function getTabGroups(): array
    {
        return PvpConstant::TAB_GROUPS;
    }

    public function getCurrentTab(): string
    {
        return $this->currentTab;
    }
}
