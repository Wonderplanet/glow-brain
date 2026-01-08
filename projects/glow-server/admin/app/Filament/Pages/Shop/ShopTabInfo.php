<?php

namespace App\Filament\Pages\Shop;

use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\ShopTabs;
use App\Filament\Authorizable;
use Filament\Pages\Page;
use Livewire\WithPagination;

class ShopTabInfo extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;

    protected static string $view = 'filament.pages.shop-tab-info';

    protected static ?string $title = 'ショップ';

    public string $currentTab = '';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::SHOP_ITEM_DISPLAY_ORDER->value; // メニューの並び順

    /**
     * タブ名と遷移先のURL
     */
    public array $tabGroups = [
        [
            '' => [
                ShopTabs::SHOP->value => 'App\Filament\Pages\MstShopItems',
                ShopTabs::SHOP_PASS->value => 'App\Filament\Pages\MstShopPasses',
            ],
        ]
    ];

    public function getTabGroups(): array
    {
        return $this->tabGroups;
    }
}
