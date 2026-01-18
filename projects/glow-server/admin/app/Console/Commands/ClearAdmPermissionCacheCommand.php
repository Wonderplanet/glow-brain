<?php

namespace App\Console\Commands;

use App\Models\Adm\AdmPermission;
use App\Services\AdminCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearAdmPermissionCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-adm-permission-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AdmPermissionの全IDに対応するファイルキャッシュを削除するコマンド';

    private AdminCacheService $adminCacheService;

    public function __construct(AdminCacheService $adminCacheService)
    {
        parent::__construct();
        $this->adminCacheService = $adminCacheService;
    }

    /**
     * AdmPermissionキャッシュ削除処理
     */
    public function handle()
    {
        try {
            $this->info('AdmPermissionキャッシュの削除を開始します...');

            // AdmPermissionの全IDを取得
            $permissionIds = AdmPermission::pluck('id')->toArray();

            if (empty($permissionIds)) {
                $this->warn('AdmPermissionのレコードが存在しません。');
                return 0;
            }

            $this->info('削除対象のPermission数: ' . count($permissionIds));

            // 各IDに対してキャッシュ削除を実行
            $deletedCount = 0;
            foreach ($permissionIds as $permissionId) {
                $this->adminCacheService->deleteAdmPermissionFeature((string)$permissionId);
                $deletedCount++;
            }

            $this->info("AdmPermissionキャッシュの削除が完了しました。");
            $this->info("削除されたキャッシュ数: {$deletedCount}");

            return 0;
        } catch (\Throwable $e) {
            $this->error('キャッシュ削除でエラーが発生しました: ' . $e->getMessage());
            Log::error('ClearAdmPermissionCacheCommand Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
