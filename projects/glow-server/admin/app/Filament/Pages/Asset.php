<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Actions\AssetAction;
use App\Filament\Authorizable;
use App\Services\ClientGitService;
use Filament\Pages\Page;

class Asset extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static string $view = 'filament.pages.asset'; // カスタムページ
    protected static ?string $navigationGroup = NavigationGroups::CLIENT_ASSET->value;
    protected static ?string $title = '管理ツール表示用アセット取り込み';
    protected static ?string $slug = 'asset';

    protected AssetAction $action;

    public function __construct($id = null)
    {
        $this->action = new AssetAction('asset');
    }

    protected function getViewData(): array
    {
        $clientGitService = app(ClientGitService::class);
        $isCloned = $clientGitService->isRepositoryCloned();

        if (!$isCloned) {
            return [
                'gitRepository' => config('admin.clientRepositoryUrl', ''),
                'gitBranch' => 'リポジトリがセットアップされていません',
                'gitCommitHash' => '',
                'gitAssetPaths' => '',
                'isCloned' => false,
            ];
        }

        $currentInfo = $clientGitService->getCurrentInfo();

        return [
            'gitRepository' => $currentInfo['repository'] ?? '',
            'gitBranch' => $currentInfo['branch'] ?? '',
            'gitCommitHash' => $currentInfo['short_hash'] ?? '',
            'gitAssetPaths' => isset($currentInfo['asset_paths']) ? implode(', ', $currentInfo['asset_paths']) : '',
            'isCloned' => true,
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->action->getActions();
    }

}
