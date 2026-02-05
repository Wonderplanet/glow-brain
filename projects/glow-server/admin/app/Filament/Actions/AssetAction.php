<?php
namespace App\Filament\Actions;

use App\Constants\AssetConstant;
use App\Filament\Pages\Asset;
use App\Services\AssetService;
use App\Services\ClientGitService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class AssetAction extends Action
{

    public function getActions()
    {
        ini_set('max_execution_time', AssetConstant::ASSET_IMPORT_MAX_EXECUTION_TIME);

        $clientGitService = new ClientGitService();
        $isCloned = $clientGitService->isRepositoryCloned();

        $actions = [];

        if (!$isCloned) {
            $actions[] = Action::make('setup')
                ->label('リポジトリをセットアップ')
                ->color('warning')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(Closure::fromCallable(function () {
                    $this->setupRepository();
                    return redirect()->to(Asset::getUrl())->with('flash_message', 'リポジトリのセットアップが完了しました。');
                }));
        } else {
            $actions[] = Action::make('import')
                ->label('ブランチを指定して取り込む')
                ->form([
                    Select::make('branch')
                        ->label('ブランチ名')
                        ->searchable()
                        ->required()
                        ->options($this->getSelectablBranches()),
                ])
                ->action(function ($data) {
                    try {
                        $this->import($data['branch'] ?? '');
                        Notification::make()
                            ->title('取り込み完了')
                            ->body('ブランチ「' . $data['branch'] . '」の取り込みが完了しました。')
                            ->success()
                            ->persistent()
                            ->send();
                        return redirect()->to(Asset::getUrl());
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('取り込み失敗')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                });
        }

        return $actions;
    }

    private function getSelectablBranches(): array
    {
        $clientGitService = new ClientGitService();

        $branches = $clientGitService->getBranches();

        // 先頭に'release'がつくブランチだけに絞る
        $branches = array_filter($branches, function ($branch) {
            return strpos($branch, AssetConstant::RELEASE_BRANCH_PREFIX) === 0;
        });
        $branches = array_combine($branches, $branches);

        return $branches;
    }

    public function setupRepository(?string $branch = null)
    {
        ini_set('max_execution_time', AssetConstant::ASSET_IMPORT_MAX_EXECUTION_TIME);

        $clientGitService = new ClientGitService();
        $clientGitService->setupRepository($branch);
    }

    public function import(string $branch)
    {
        ini_set('max_execution_time', AssetConstant::ASSET_IMPORT_MAX_EXECUTION_TIME);

        $assetService = app(AssetService::class);
        $assetService->import($branch);
    }
}
