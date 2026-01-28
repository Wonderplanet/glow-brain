<?php

namespace App\Filament\Pages\Shop;

use App\Constants\ShopTabs;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

/**
 * ショップ画面の基底クラス
 */
abstract class ShopDataBasePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.shop-tab-info';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '';

    /**
     * タブ名と遷移先のURL
     */
    public array $tabGroups = [
        [
            '' => [
                ShopTabs::SHOP->value => 'App\Filament\Pages\MstShopItems',
                ShopTabs::SHOP_PASS->value => 'App\Filament\Pages\MstShopPasses',
            ]
        ]
    ];

    /**
     * ヘッダーで表示されるタブ名
     * 各ページでこのプロパティを上書きする
     */
    public string $currentTab = '';

    public function getTabGroups(): array
    {
        return $this->tabGroups;
    }

    public function getCurrentTab(): string
    {
        return $this->currentTab;
    }

    public function getHeader(): ?View
    {
        return view('filament/common/shop-tab');
    }
}
