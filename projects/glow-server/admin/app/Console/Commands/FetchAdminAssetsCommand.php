<?php

namespace App\Console\Commands;

use App\Services\AssetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchAdminAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-admin-assets {--branch=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'glow-clientリポジトリのアセット取り込みコマンド（sparse-checkout使用）';

    /**
     * アセット取り込み処理
     */
    public function handle()
    {
        $assetService = app(AssetService::class);

        $branch = $this->option('branch');

        try {
            // アセット取り込み実行
            $this->info('アセット取り込みを開始します...');
            $this->info('ブランチ: ' . ($branch ?: 'デフォルト'));

            $assetService->import($branch);

            $this->info('アセット取り込みが完了しました。');
        } catch (\Throwable $e) {
            $this->error('アセット取り込みでエラーが発生しました: ' . $e->getMessage());
            Log::error('FetchAdminAssetsCommand Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
