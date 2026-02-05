<?php

declare(strict_types=1);

namespace App\Console\Commands\AppStore;

use Illuminate\Console\Command;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreEnvironmentValidator;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\AppStoreServerApiService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\CertificateManager;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit2ToLegacyReceiptConverter;

/**
 * StoreKit2総合診断コマンド
 * StoreKit2の全体フローを段階的に診断し、問題箇所を特定
 */
class DebugStoreKit2DiagnosisCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'appstore:debug-diagnosis 
                           {transactionId : 診断対象のトランザクションID} 
                           {--environment=production : 環境 (production|sandbox)}
                           {--quick : 簡易診断モード（時間のかかるテストをスキップ）}
                           {--report : 診断レポートをファイルに保存}
                           {--product-id= : 期待するプロダクトID（lookupテスト用、必須）}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = 'StoreKit2全体フローの総合診断・問題箇所特定';

    /** @var array<string, mixed> */
    private array $diagnosticResults = [];
    /** @var array<int, string> */
    private array $recommendations = [];
    /** @var array<int, string> */
    private array $criticalIssues = [];

    public function __construct(
        private AppStoreServerApiService $appStoreServerApiService,
        private JwsService $jwsService,
        private CertificateManager $certificateManager,
        private StoreKit2ToLegacyReceiptConverter $converter
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
        $quickMode = $this->option('quick');
        $saveReport = $this->option('report');
        $productId = $this->option('product-id');

        // 環境値の検証
        if (!in_array($environment, ['production', 'sandbox'], true)) {
            $this->error('環境は production または sandbox を指定してください');
            return self::FAILURE;
        }

        // productIdの検証
        if (!$productId) {
            $this->error('lookupテストにはproductIdの指定が必要です。--product-idオプションを使用してください');
            return self::FAILURE;
        }

        // 環境値を定数に変換
        $envConstant = $environment === 'sandbox'
            ? AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
            : AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION;

        // 注入されたサービスの存在確認
        $jwsServiceClass = get_class($this->jwsService);
        $certificateManagerClass = get_class($this->certificateManager);

        $this->info("=== StoreKit2 総合診断 ===");
        $this->info("対象トランザクション: {$transactionId}");
        $this->info("環境: {$environment}");
        $this->info("診断モード: " . ($quickMode ? '簡易' : '詳細'));
        $this->line('');

        try {
            // 診断フェーズ1: 基本設定確認
            $this->runDiagnosticPhase1($envConstant);

            // 診断フェーズ2: API疎通確認
            $this->runDiagnosticPhase2($transactionId, $envConstant, $productId, $quickMode);

            // 診断フェーズ3: データ取得・解析
            $this->runDiagnosticPhase3($transactionId, $envConstant, $productId);

            // 診断フェーズ4: 変換処理確認
            $this->runDiagnosticPhase4($transactionId, $envConstant);

            // 診断フェーズ5: 総合評価
            $this->runDiagnosticPhase5();

            // レポート保存
            if ($saveReport) {
                $this->saveReport($transactionId, $environment);
            }
        } catch (\Exception $e) {
            $this->error('❌ 診断中にエラーが発生しました: ' . $e->getMessage());
            $this->criticalIssues[] = "診断実行エラー: {$e->getMessage()}";

            if ($this->option('verbose')) {
                $this->error('詳細: ' . $e->getTraceAsString());
            }

            return self::FAILURE;
        }

        // 最終結果判定
        return count($this->criticalIssues) === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * 診断フェーズ1: 基本設定確認
     */
    private function runDiagnosticPhase1(string $environment): void
    {
        $this->info('🔍 フェーズ1: 基本設定確認');

        try {
            // API設定確認
            $baseUrl = $this->appStoreServerApiService->getApiBaseUrl($environment);
            $this->displayResult('APIベースURL', $baseUrl, 'success');

            // 環境設定確認
            $isSandbox = $environment === AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX;
            $expectedUrl = $isSandbox ?
                'https://api.storekit-sandbox.itunes.apple.com' :
                'https://api.storekit.itunes.apple.com';

            if ($baseUrl === $expectedUrl) {
                $this->displayResult('環境設定', '✅ 正常', 'success');
                $this->diagnosticResults['environment_config'] = 'OK';
            } else {
                $this->displayResult('環境設定', '❌ URL不一致', 'error');
                $this->criticalIssues[] = "環境設定: 期待URL={$expectedUrl}, 実際URL={$baseUrl}";
                $this->diagnosticResults['environment_config'] = 'ERROR';
            }

            // 設定ファイル確認
            $this->checkConfigurationFiles();
        } catch (\Exception $e) {
            $this->displayResult('基本設定確認', '❌ エラー: ' . $e->getMessage(), 'error');
            $this->criticalIssues[] = "基本設定確認エラー: {$e->getMessage()}";
            $this->diagnosticResults['basic_config'] = 'ERROR';
        }

        $this->line('');
    }

    /**
     * 診断フェーズ2: API疎通確認
     */
    private function runDiagnosticPhase2(
        string $transactionId,
        string $environment,
        string $productId,
        bool $quickMode
    ): void {
        $this->info('🔍 フェーズ2: API疎通確認');

        // Lookup API テスト
        $this->testApiEndpoint('Lookup API', function () use ($transactionId, $environment, $productId) {
            return $this->appStoreServerApiService->lookup($transactionId, $environment, $productId);
        });

        // History API テスト（簡易モードでなければ実行）
        if (!$quickMode) {
            $this->testApiEndpoint('History API', function () use ($transactionId, $environment) {
                return $this->appStoreServerApiService->getTransactionHistory($transactionId, $environment);
            });
        } else {
            $this->displayResult('History API', '⏭️ 簡易モードのためスキップ', 'info');
        }

        $this->line('');
    }

    /**
     * 診断フェーズ3: データ取得・解析
     */
    private function runDiagnosticPhase3(string $transactionId, string $environment, string $productId): void
    {
        $this->info('🔍 フェーズ3: データ取得・解析');

        try {
            // トランザクションデータ取得
            $transactionData = $this->appStoreServerApiService->lookup($transactionId, $environment, $productId);
            $this->diagnosticResults['transaction_data'] = $transactionData;

            // データ構造確認
            $this->analyzeTransactionData($transactionData);

            // 日時データ確認
            $this->analyzeDateTimeFields($transactionData);
        } catch (\Exception $e) {
            $this->displayResult('データ取得・解析', '❌ エラー: ' . $e->getMessage(), 'error');
            $this->criticalIssues[] = "データ取得エラー: {$e->getMessage()}";
        }

        $this->line('');
    }

    /**
     * 診断フェーズ4: 変換処理確認
     */
    private function runDiagnosticPhase4(string $transactionId, string $environment): void
    {
        $this->info('🔍 フェーズ4: 変換処理確認');

        try {
            if (!isset($this->diagnosticResults['transaction_data'])) {
                $this->displayResult('変換処理', '❌ 元データなし', 'error');
                return;
            }

            $originalData = $this->diagnosticResults['transaction_data'];

            // レガシー形式変換テスト
            $convertedReceipt = $this->converter->convert($originalData);
            $this->displayResult('レガシー変換', '✅ 成功', 'success');
            $this->diagnosticResults['converted_receipt'] = $convertedReceipt;

            // 変換結果検証
            $this->validateConvertedData($originalData, $convertedReceipt);
        } catch (\Exception $e) {
            $this->displayResult('変換処理', '❌ エラー: ' . $e->getMessage(), 'error');
            $this->criticalIssues[] = "変換処理エラー: {$e->getMessage()}";
        }

        $this->line('');
    }

    /**
     * 診断フェーズ5: 総合評価
     */
    private function runDiagnosticPhase5(): void
    {
        $this->info('🔍 フェーズ5: 総合評価');

        // 診断結果サマリー
        $this->displayDiagnosticSummary();

        // 推奨事項表示
        $this->displayRecommendations();

        // 問題があれば対処方法表示
        if (count($this->criticalIssues) > 0) {
            $this->displayTroubleshooting();
        }

        $this->line('');
    }

    /**
     * API エンドポイントテスト
     */
    private function testApiEndpoint(string $endpointName, callable $apiCall): void
    {
        try {
            $startTime = microtime(true);
            $result = $apiCall();
            $endTime = microtime(true);

            $responseTime = round(($endTime - $startTime) * 1000, 2);
            $this->displayResult($endpointName, "✅ 成功 ({$responseTime}ms)", 'success');
            $this->diagnosticResults[strtolower(str_replace(' ', '_', $endpointName))] = 'OK';
        } catch (\Exception $e) {
            $this->displayResult($endpointName, '❌ 失敗: ' . $e->getMessage(), 'error');
            $this->criticalIssues[] = "{$endpointName}エラー: {$e->getMessage()}";
            $this->diagnosticResults[strtolower(str_replace(' ', '_', $endpointName))] = 'ERROR';
        }
    }

    /**
     * 設定ファイル確認
     */
    private function checkConfigurationFiles(): void
    {
        // 主要な設定値の確認
        $configs = [
            'wp_currency.store.app_store.storekit2.external_token_url',
            'wp_currency.store.app_store.storekit2.enable_history_fallback',
        ];

        foreach ($configs as $configKey) {
            $value = config($configKey);
            $status = $value !== null ? '✅ 設定済み' : '⚠️ 未設定';
            $this->displayResult($configKey, $status, $value !== null ? 'success' : 'warning');
        }
    }

    /**
     * トランザクションデータ解析
     *
     * @param array<string, mixed> $data
     */
    private function analyzeTransactionData(array $data): void
    {
        $requiredFields = ['transactionId', 'productId', 'bundleId', 'environment', 'purchaseDate'];

        foreach ($requiredFields as $field) {
            $status = isset($data[$field]) ? '✅ 存在' : '❌ 不足';
            $this->displayResult("必須フィールド: {$field}", $status, isset($data[$field]) ? 'success' : 'error');

            if (!isset($data[$field])) {
                $this->criticalIssues[] = "必須フィールド不足: {$field}";
            }
        }
    }

    /**
     * 日時フィールド解析
     *
     * @param array<string, mixed> $data
     */
    private function analyzeDateTimeFields(array $data): void
    {
        $dateFields = ['purchaseDate', 'originalPurchaseDate', 'expiresDate'];

        foreach ($dateFields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                if (is_numeric($value)) {
                    $formatted = date('Y-m-d H:i:s', (int)($value / 1000));
                    $this->displayResult("日時フィールド: {$field}", "✅ エポック時間 → {$formatted}", 'success');
                } elseif (is_string($value) && strtotime($value) !== false) {
                    $this->displayResult("日時フィールド: {$field}", "✅ ISO8601形式", 'success');
                } else {
                    $this->displayResult("日時フィールド: {$field}", "❌ 無効な形式", 'error');
                    $this->criticalIssues[] = "無効な日時形式: {$field} = {$value}";
                }
            }
        }
    }

    /**
     * 変換データ検証
     *
     * @param array<string, mixed> $original
     * @param array<string, mixed> $converted
     */
    private function validateConvertedData(array $original, array $converted): void
    {
        // レガシー形式の必須フィールド確認
        $requiredLegacyFields = ['receipt_type', 'in_app', 'bundle_id', 'environment'];

        foreach ($requiredLegacyFields as $field) {
            $status = isset($converted[$field]) ? '✅ 存在' : '❌ 不足';
            $this->displayResult("レガシーフィールド: {$field}", $status, isset($converted[$field]) ? 'success' : 'error');
        }

        // データ整合性確認
        if (isset($original['transactionId']) && isset($converted['in_app'][0]['transaction_id'])) {
            $match = $original['transactionId'] === $converted['in_app'][0]['transaction_id'];
            $this->displayResult('ID整合性', $match ? '✅ 一致' : '❌ 不一致', $match ? 'success' : 'error');

            if (!$match) {
                $originalId = $original['transactionId'];
                $convertedId = $converted['in_app'][0]['transaction_id'];
                $this->criticalIssues[] = "トランザクションID不一致: {$originalId} ≠ {$convertedId}";
            }
        }
    }

    /**
     * 診断結果サマリー表示
     */
    private function displayDiagnosticSummary(): void
    {
        $this->info('📋 診断結果サマリー:');

        $totalChecks = count($this->diagnosticResults);
        $successCount = count(array_filter($this->diagnosticResults, fn($result) => $result === 'OK'));
        $errorCount = count(array_filter($this->diagnosticResults, fn($result) => $result === 'ERROR'));

        $this->displayResult('総チェック数', (string)$totalChecks, 'info');
        $this->displayResult('成功', (string)$successCount, 'success');
        $this->displayResult('エラー', (string)$errorCount, $errorCount > 0 ? 'error' : 'success');
        $successRate = round(($successCount / $totalChecks) * 100, 1) . '%';
        $rateStatus = $successCount === $totalChecks ? 'success' : 'warning';
        $this->displayResult('成功率', $successRate, $rateStatus);
    }

    /**
     * 推奨事項表示
     */
    private function displayRecommendations(): void
    {
        if (count($this->recommendations) === 0) {
            $this->recommendations = [
                '定期的にこの診断を実行してシステム状態を監視する',
                'Rate Limit エラーに対するフォールバック機能を有効にする',
                '証明書の有効期限を監視する',
                'ログ出力レベルを適切に設定する',
            ];
        }

        $this->line('');
        $this->info('💡 推奨事項:');
        foreach ($this->recommendations as $recommendation) {
            $this->line("  • {$recommendation}");
        }
    }

    /**
     * トラブルシューティング表示
     */
    private function displayTroubleshooting(): void
    {
        $this->line('');
        $this->error('🚨 発見された問題:');
        foreach ($this->criticalIssues as $issue) {
            $this->error("  • {$issue}");
        }

        $this->line('');
        $this->info('🔧 対処方法:');
        $this->line('  1. 設定ファイル（config/wp_currency.php）を確認');
        $this->line('  2. App Store Connect API キーの設定を確認');
        $this->line('  3. ネットワーク接続を確認');
        $this->line('  4. verbose オプション(-v)で詳細ログを確認');
        $this->line('  5. 個別のデバッグコマンドで詳細調査を実行');
    }

    /**
     * レポート保存
     */
    private function saveReport(string $transactionId, string $environment): void
    {
        try {
            $reportData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'transaction_id' => $transactionId,
                'environment' => $environment,
                'diagnostic_results' => $this->diagnosticResults,
                'critical_issues' => $this->criticalIssues,
                'recommendations' => $this->recommendations,
            ];

            $filename = "storekit2_diagnosis_" . date('Y-m-d_H-i-s') . ".json";
            $filepath = storage_path("logs/{$filename}");

            file_put_contents($filepath, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("📄 診断レポートを保存しました: {$filepath}");
        } catch (\Exception $e) {
            $this->warn("⚠️ レポート保存に失敗しました: {$e->getMessage()}");
        }
    }

    /**
     * 結果表示ヘルパー
     */
    private function displayResult(string $item, string $result, string $type): void
    {
        $icon = match ($type) {
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '•'
        };

        $this->line(sprintf('  %-25s : %s %s', $item, $icon, $result));
    }
}
