<?php

namespace App\Providers;

use App\Domain\Reward\Managers\RewardManager;
use App\Facades\Promotion;
use App\Services\Filament\AdminPanelService;
use App\Services\PromotionService;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\ArgvInput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // RewardManager
        $this->app->scoped(RewardManager::class, function () {
            return new RewardManager();
        });
        $this->app->bind(
            \App\Domain\Reward\Managers\RewardManagerInterface::class,
            RewardManager::class
        );

        // Promotion
        $this->app->scoped(Promotion::FACADE_ACCESSOR, PromotionService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->runningInConsole()) {
            // 特定のコマンド実行時、管理ツールで設定中のリリースバージョンDBに接続
            $input = new ArgvInput();
            $commandName = $input->getFirstArgument();
            // 接頭辞で指定するコマンド
            $targetCommandPrefixes = [
                'app:',
            ];

            // コマンド名全体で指定するコマンド
            $targetCommands = [
            ];

            $isTargetCommand = false;

            // 接頭辞チェック
            foreach ($targetCommandPrefixes as $prefix) {
                if (str_starts_with($commandName, $prefix)) {
                    $isTargetCommand = true;
                    break;
                }
            }

            // コマンド名全体チェック
            if (!$isTargetCommand && in_array($commandName, $targetCommands)) {
                $isTargetCommand = true;
            }

            if ($isTargetCommand) {
                /** @var AdminPanelService $adminPanelService */
                $adminPanelService = $this->app->make(AdminPanelService::class);
                $adminPanelService->setMstDatabaseConnection();
            }
        }
    }
}
