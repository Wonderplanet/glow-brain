<?php

declare(strict_types=1);

namespace App\Console\Commands\AppStore;

use Illuminate\Console\Command;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService;

/**
 * App Store Server API エンドポイントテストコマンド
 * 各APIエンドポイントの疎通確認とレスポンス検証
 */
class DebugApiEndpointsCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'appstore:debug-endpoints 
                           {transactionId : テスト用トランザクションID} 
                           {--environment=production : 環境 (production|sandbox)}
                           {--endpoint=all : テストするエンドポイント (all|lookup|history|subscription)}
                           {--timeout=30 : タイムアウト秒数}
                           {--product-id= : 期待するプロダクトID（lookupテスト用）}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = 'App Store Server API各エンドポイントの疎通テスト';

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
        $targetEndpoint = $this->option('endpoint');
        $timeout = (int)$this->option('timeout');
        $productId = $this->option('product-id');

        // 環境値の検証
        if (!in_array($environment, ['production', 'sandbox'], true)) {
            $this->error('環境は production または sandbox を指定してください');
            return self::FAILURE;
        }

        // lookupテストでproductIdが必要
        if (($targetEndpoint === 'all' || $targetEndpoint === 'lookup') && !$productId) {
            $this->error('lookupテストにはproductIdの指定が必要です。--product-idオプションを使用してください');
            return self::FAILURE;
        }

        // 環境値を定数に変換
        $envConstant = $environment === 'sandbox'
            ? AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
            : AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION;

        $this->info("=== App Store Server API エンドポイント疎通テスト ===");
        $this->info("テスト対象: {$targetEndpoint}");
        $this->info("環境: {$environment}");
        $this->info("トランザクションID: {$transactionId}");
        $this->info("タイムアウト: {$timeout}秒");
        if ($productId) {
            $this->info("期待するプロダクトID: {$productId}");
        }
        $this->line('');

        $allSuccess = true;
        $hasTests = false;

        try {
            // APIベースURLの確認
            $this->info('🔍 Step 1: API基本情報確認');
            $this->verifyApiBaseInfo($envConstant);
            $this->line('');

            // エンドポイント別テスト実行
            if ($targetEndpoint === 'all' || $targetEndpoint === 'lookup') {
                $this->info('🔍 Step 2: Lookup API (/v1/transactions) テスト');
                $success = $this->testLookupEndpoint($transactionId, $envConstant, $productId, $timeout);
                if (!$success) {
                    $allSuccess = false;
                }
                $hasTests = true;
                $this->line('');
            }

            if ($targetEndpoint === 'all' || $targetEndpoint === 'history') {
                $this->info('🔍 Step 3: History API (/v2/history) テスト');
                $success = $this->testHistoryEndpoint($transactionId, $envConstant, $timeout);
                if (!$success) {
                    $allSuccess = false;
                }
                $hasTests = true;
                $this->line('');
            }

            if ($targetEndpoint === 'all' || $targetEndpoint === 'subscription') {
                $this->info('🔍 Step 4: Subscription Status API (/v1/subscriptions) テスト');
                $success = $this->testSubscriptionEndpoint($transactionId, $envConstant, $timeout);
                if (!$success) {
                    $allSuccess = false;
                }
                $hasTests = true;
                $this->line('');
            }

            // テストが実行されていない場合の処理
            if (!$hasTests) {
                $this->warn('⚠️ 指定されたエンドポイントでテストが実行されませんでした');
                $allSuccess = false;
            }

            // 総合結果
            $this->info('🔍 Step 5: 総合テスト結果');
            $this->displayOverallResult($allSuccess);
        } catch (\Exception $e) {
            $this->error('❌ エラーが発生しました: ' . $e->getMessage());
            if ($this->option('verbose')) {
                $this->error('詳細: ' . $e->getTraceAsString());
            }
            return self::FAILURE;
        }

        return $allSuccess ? self::SUCCESS : self::FAILURE;
    }

    /**
     * API基本情報確認
     */
    private function verifyApiBaseInfo(string $environment): void
    {
        $baseUrl = $this->appStoreServerApiService->getApiBaseUrl($environment);
        $this->displayKeyValue('APIベースURL', $baseUrl);

        // 環境判定
        $isSandbox = $environment === AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX;
        $this->displayKeyValue('環境設定', $isSandbox ? 'Sandbox' : 'Production');

        $this->displayKeyValue('期待されるURL', $isSandbox ?
            'https://api.storekit-sandbox.itunes.apple.com' :
            'https://api.storekit.itunes.apple.com');
    }

    /**
     * Lookup API テスト
     */
    private function testLookupEndpoint(
        string $transactionId,
        string $environment,
        string $productId,
        int $timeout
    ): bool {
        try {
            $startTime = microtime(true);

            $this->line('  📡 API呼び出し実行中...');
            $result = $this->appStoreServerApiService->lookup($transactionId, $environment, $productId);

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            $this->info('  ✅ 成功');
            $this->displayKeyValue('  レスポンス時間', $responseTime . 'ms');
            $this->displayKeyValue('  トランザクションID', $result['transactionId'] ?? 'N/A');
            $this->displayKeyValue('  プロダクトID', $result['productId'] ?? 'N/A');
            $this->displayKeyValue('  環境', $result['environment'] ?? 'N/A');

            // レスポンス詳細（verbose時）
            if ($this->option('verbose')) {
                $this->line('');
                $this->info('  🔍 完全なレスポンス:');
                $jsonResponse = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->line($jsonResponse ?: '{}');
            }

            return true;
        } catch (\Exception $e) {
            $this->error('  ❌ 失敗: ' . $e->getMessage());

            // エラーコード別の診断
            $this->diagnoseLookupError($e);

            return false;
        }
    }

    /**
     * History API テスト
     */
    private function testHistoryEndpoint(string $transactionId, string $environment, int $timeout): bool
    {
        try {
            $startTime = microtime(true);

            $this->line('  📡 API呼び出し実行中...');
            $results = $this->appStoreServerApiService->getTransactionHistory($transactionId, $environment);

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            $this->info('  ✅ 成功');
            $this->displayKeyValue('  レスポンス時間', $responseTime . 'ms');
            $this->displayKeyValue('  履歴件数', (string)count($results));

            if (count($results) > 0) {
                $firstTransaction = $results[0];
                $this->displayKeyValue('  最初のトランザクションID', $firstTransaction['transactionId'] ?? 'N/A');
                $this->displayKeyValue('  最初のプロダクトID', $firstTransaction['productId'] ?? 'N/A');
            }

            // レスポンス詳細（verbose時）
            if ($this->option('verbose')) {
                $this->line('');
                $this->info('  🔍 完全なレスポンス:');
                $jsonResponse = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->line($jsonResponse ?: '[]');
            }

            return true;
        } catch (\Exception $e) {
            $this->error('  ❌ 失敗: ' . $e->getMessage());

            // エラーコード別の診断
            $this->diagnoseHistoryError($e);

            return false;
        }
    }

    /**
     * Subscription Status API テスト（将来の拡張用）
     */
    private function testSubscriptionEndpoint(string $transactionId, string $environment, int $timeout): bool
    {
        $this->line('  📋 Subscription Status APIはサポートされていません');
        $this->line('  このエンドポイントは現在の実装に含まれていません。');

        // 現在はスキップとして成功扱い
        return true;
    }

    /**
     * Lookup APIエラー診断
     */
    private function diagnoseLookupError(\Exception $e): void
    {
        $message = $e->getMessage();

        $this->line('');
        $this->info('  🔍 エラー診断:');

        if (str_contains($message, '429')) {
            $this->line('  • Rate Limit エラーが発生しています');
            $this->line('  • しばらく時間をおいて再試行してください');
            $this->line('  • フォールバック機能が有効かconfig確認してください');
        } elseif (str_contains($message, '404')) {
            $this->line('  • トランザクションIDが見つかりません');
            $this->line('  • トランザクションIDが正しいか確認してください');
            $this->line('  • 環境設定（production/sandbox）が正しいか確認してください');
        } elseif (str_contains($message, '401') || str_contains($message, '403')) {
            $this->line('  • 認証エラーが発生しています');
            $this->line('  • App Store Connect APIキーの設定を確認してください');
            $this->line('  • JWTトークンの生成が正しく行われているか確認してください');
        } elseif (str_contains($message, 'timeout') || str_contains($message, 'connection')) {
            $this->line('  • ネットワーク接続の問題です');
            $this->line('  • インターネット接続を確認してください');
            $this->line('  • ファイアウォール設定を確認してください');
        } else {
            $this->line('  • 予期しないエラーです');
            $this->line('  • verbose オプション(-v)で詳細情報を確認してください');
        }
    }

    /**
     * History APIエラー診断
     */
    private function diagnoseHistoryError(\Exception $e): void
    {
        $message = $e->getMessage();

        $this->line('');
        $this->info('  🔍 エラー診断:');

        if (str_contains($message, '429')) {
            $this->line('  • Rate Limit エラーが発生しています');
            $this->line('  • History APIは特にRate Limitが厳しいエンドポイントです');
            $this->line('  • 十分な間隔をあけて再試行してください');
        } elseif (str_contains($message, '404')) {
            $this->line('  • 履歴データが見つかりません');
            $this->line('  • トランザクションIDが正しいか確認してください');
            $this->line('  • 古いトランザクションは履歴から削除されている可能性があります');
        } else {
            $this->diagnoseLookupError($e); // 共通のエラー診断を使用
        }
    }

    /**
     * 総合結果表示
     */
    private function displayOverallResult(bool $allSuccess): void
    {
        if ($allSuccess) {
            $this->info('🎉 全てのテストが成功しました');
            $this->line('');
            $this->info('✅ APIエンドポイントは正常に動作しています');
            $this->line('💡 推奨事項:');
            $this->line('  • 定期的にこのテストを実行してAPI状態を監視してください');
            $this->line('  • 本番環境では適切なRate Limit対策を実装してください');
        } else {
            $this->error('❌ 一部またはすべてのテストが失敗しました');
            $this->line('');
            $this->info('🔧 対処方法:');
            $this->line('  • 上記のエラー診断を参考に問題を解決してください');
            $this->line('  • 設定ファイル（config/wp_currency.php）を確認してください');
            $this->line('  • App Store Connect API キーの設定を確認してください');
            $this->line('  • verbose オプション(-v)で詳細ログを確認してください');
        }
    }

    /**
     * キー・バリューペアを整列して表示
     */
    private function displayKeyValue(string $key, string $value): void
    {
        $this->line(sprintf('  %-20s : %s', $key, $value));
    }
}
