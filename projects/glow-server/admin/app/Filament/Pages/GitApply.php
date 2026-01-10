<?php

namespace App\Filament\Pages;

use App\Constants\MasterDataManagementDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Actions\GitApplyAction;
use Filament\Pages\Page;

class GitApply extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.git-apply'; // カスタムページ
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_MANAGEMENT->value;
    protected static ?string $title = 'データ適用（Git）';
    protected static ?string $slug = 'git-apply';
    protected static ?int $navigationSort = MasterDataManagementDisplayOrder::GIT_APPLY_DISPLAY_ORDER->value; // メニューの並び順

    // ナビゲーションパネルに表示しないようにする
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected GitApplyAction $action;

    public function __construct($id = null)
    {
        $this->action = new GitApplyAction('git-apply');
    }

    protected function getViewData(): array
    {
#        $hash = $this->action->getCurrentHash();
        $gitBranch = env('GIT_BRANCH');

        return [
            'gitBranch' => $gitBranch,
#            'hash' => $hash,
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->action->getActions();
    }
}
