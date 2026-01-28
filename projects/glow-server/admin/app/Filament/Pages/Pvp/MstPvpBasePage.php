<?php

namespace App\Filament\Pages\Pvp;

use App\Constants\PvpConstant;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

/**
 * ランクマッチ情報画面の基底クラス
 */
abstract class MstPvpBasePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.pvp-tab-info';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '';

    /**
     * ヘッダーで表示されるタブ名
     * 各ページでこのプロパティを上書きする
     */
    public string $currentTab = '';

    public function getTabGroups(): array
    {
        return PvpConstant::TAB_GROUPS;
    }

    public function getCurrentTab(): string
    {
        return $this->currentTab;
    }

    public function getHeader(): ?View
    {
        return view('filament/common/pvp-tab');
    }
}
