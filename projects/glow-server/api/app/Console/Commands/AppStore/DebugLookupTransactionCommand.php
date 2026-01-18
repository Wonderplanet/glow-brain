<?php

declare(strict_types=1);

namespace App\Console\Commands\AppStore;

use Illuminate\Console\Command;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService;

/**
 * App Store Server API トランザクション情報取得デバッグコマンド
 */
class DebugLookupTransactionCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'appstore:debug-lookup-transaction 
                           {transactionId : トランザクションID} 
                           {--environment=production : 環境 (production|sandbox)}
                           {--history : 履歴API(/v2/history)を使用}
                           {--product-id= : 期待するプロダクトID（lookup API使用時に必須）}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = 'App Store Server APIでトランザクション情報を取得・表示（デバッグ用）';

    public function __construct(
        private AppStoreServerApiService $appStoreServerApiService
    ) {
        parent::__construct();
    }

    /**
     * コマンド実行
     */
    public function handle(): int
    {
        $transactionId = $this->argument('transactionId');
        $environment = $this->option('environment');
        $useHistory = $this->option('history');
        $productId = $this->option('product-id');

        // 環境値の検証
        if (!in_array($environment, ['production', 'sandbox'], true)) {
            $this->error('環境は production または sandbox を指定してください');
            return self::FAILURE;
        }

        // lookup API使用時はproductIdが必須
        if (!$useHistory && !$productId) {
            $this->error('lookup API使用時はproductIdの指定が必要です。--product-idオプションを使用してください');
            return self::FAILURE;
        }

        // 環境値を定数に変換
        $envConstant = $environment === 'sandbox'
            ? AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
            : AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION;

        $this->info("=== App Store Server API トランザクション情報取得 ===");
        $this->info("トランザクションID: {$transactionId}");
        $this->info("環境: {$environment}");
        $this->info("API: " . ($useHistory ? '/v2/history' : '/v1/transactions'));
        $this->line('');

        try {
            if ($useHistory) {
                // 履歴API使用
                $this->info('🔍 履歴API(/v2/history)でトランザクション情報を取得中...');
                $historyData = $this->appStoreServerApiService->getTransactionHistory($transactionId, $envConstant);

                $this->info("✅ 取得成功！履歴件数: " . count($historyData));
                $this->line('');

                if (count($historyData) === 0) {
                    $this->warn('⚠️ 履歴が空です');
                    return self::SUCCESS;
                }

                // 各履歴データを表示
                foreach ($historyData as $index => $transactionInfo) {
                    $this->displayTransactionInfo($transactionInfo, $index + 1, count($historyData));
                    if ($index < count($historyData) - 1) {
                        $this->line(str_repeat('-', 80));
                    }
                }
            } else {
                // 通常のlookup API使用
                $this->info('🔍 Lookup API(/v1/transactions)でトランザクション情報を取得中...');
                $transactionInfo = $this->appStoreServerApiService->lookup($transactionId, $envConstant, $productId);

                $this->info('✅ 取得成功！');
                $this->line('');

                $this->displayTransactionInfo($transactionInfo);
            }
        } catch (\Exception $e) {
            $this->error('❌ エラーが発生しました: ' . $e->getMessage());
            if ($this->option('verbose')) {
                $this->error('詳細: ' . $e->getTraceAsString());
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * トランザクション情報を表示
     *
     * @param array<string, mixed> $transactionInfo
     */
    private function displayTransactionInfo(array $transactionInfo, ?int $index = null, ?int $total = null): void
    {
        if ($index !== null && $total !== null) {
            $this->info("📄 トランザクション情報 ({$index}/{$total})");
        } else {
            $this->info('📄 トランザクション情報');
        }

        // 主要な情報を整理して表示
        $this->displayKeyValue('トランザクションID', $transactionInfo['transactionId'] ?? 'N/A');
        $this->displayKeyValue('オリジナルトランザクションID', $transactionInfo['originalTransactionId'] ?? 'N/A');
        $this->displayKeyValue('プロダクトID', $transactionInfo['productId'] ?? 'N/A');
        $this->displayKeyValue('バンドルID', $transactionInfo['bundleId'] ?? 'N/A');

        // 購入日時の表示（エポック時間の場合は日時変換）
        $purchaseDate = $transactionInfo['purchaseDate'] ?? 'N/A';
        if (is_numeric($purchaseDate)) {
            $purchaseDateFormatted = date('Y-m-d H:i:s', (int)($purchaseDate / 1000))
                . ' (エポック: ' . $purchaseDate . ')';
        } else {
            $purchaseDateFormatted = $purchaseDate;
        }
        $this->displayKeyValue('購入日時', $purchaseDateFormatted);

        $this->displayKeyValue('数量', $transactionInfo['quantity'] ?? 'N/A');
        $this->displayKeyValue('タイプ', $transactionInfo['type'] ?? 'N/A');
        $this->displayKeyValue('環境', $transactionInfo['environment'] ?? 'N/A');

        // 期限切れ日時（サブスクリプションの場合）
        if (isset($transactionInfo['expiresDate'])) {
            $expiresDate = $transactionInfo['expiresDate'];
            if (is_numeric($expiresDate)) {
                $expiresDateFormatted = date('Y-m-d H:i:s', (int)($expiresDate / 1000))
                    . ' (エポック: ' . $expiresDate . ')';
            } else {
                $expiresDateFormatted = $expiresDate;
            }
            $this->displayKeyValue('期限切れ日時', $expiresDateFormatted);
        }

        $this->line('');
        $this->info('🔍 完全なトランザクション情報 (JSON):');
        $this->line(json_encode($transactionInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * キー・バリューペアを整列して表示
     */
    private function displayKeyValue(string $key, string $value): void
    {
        $this->line(sprintf('  %-20s : %s', $key, $value));
    }
}
