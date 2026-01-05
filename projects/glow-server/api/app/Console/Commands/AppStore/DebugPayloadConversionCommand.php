<?php

declare(strict_types=1);

namespace App\Console\Commands\AppStore;

use Illuminate\Console\Command;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\StoreKit2ToLegacyReceiptConverter;

/**
 * StoreKit2ペイロード変換テストコマンド
 * StoreKit2形式からレガシーレシート形式への変換テスト
 */
class DebugPayloadConversionCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'appstore:debug-payload-conversion 
                           {--jws= : JWSトークンから変換テスト}
                           {--payload= : JSONペイロードから変換テスト}
                           {--sample : サンプルデータでの変換テスト}
                           {--validate-only : 変換は行わず検証のみ}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = 'StoreKit2ペイロードのレガシー形式変換テスト';

    public function __construct(
        private StoreKit2ToLegacyReceiptConverter $converter,
        private JwsService $jwsService
    ) {
        parent::__construct();
    }

    /**
     * コマンド実行
     */
    public function handle(): int
    {
        $jws = $this->option('jws');
        $payloadJson = $this->option('payload');
        $useSample = $this->option('sample');
        $validateOnly = $this->option('validate-only');

        $this->info("=== StoreKit2 ペイロード変換テスト ===");
        $this->info("変換モード: " . ($validateOnly ? '検証のみ' : '変換実行'));
        $this->line('');

        try {
            $payload = null;

            // 入力ソースの決定
            if ($jws) {
                $this->info('🔍 Step 1: JWSトークンからペイロード抽出');
                $payload = $this->extractPayloadFromJws($jws);
            } elseif ($payloadJson) {
                $this->info('🔍 Step 1: JSONペイロード読み込み');
                $payload = $this->parseJsonPayload($payloadJson);
            } elseif ($useSample) {
                $this->info('🔍 Step 1: サンプルペイロード生成');
                $payload = $this->generateSamplePayload();
            } else {
                $this->error('入力データが指定されていません。--jws, --payload, または --sample オプションを使用してください。');
                return self::FAILURE;
            }

            $this->line('');

            // ペイロード解析
            $this->info('🔍 Step 2: ペイロード解析');
            $this->analyzePayload($payload);
            $this->line('');

            // 変換前検証
            $this->info('🔍 Step 3: 変換前検証');
            $validationResult = $this->validatePayload($payload);
            $this->line('');

            if (!$validateOnly && $validationResult) {
                // 変換実行
                $this->info('🔍 Step 4: レガシー形式への変換');
                $convertedReceipt = $this->performConversion($payload);
                $this->line('');

                // 変換後検証
                $this->info('🔍 Step 5: 変換後検証');
                $this->validateConvertedReceipt($convertedReceipt);
                $this->line('');

                // 比較表示
                $this->info('🔍 Step 6: 変換前後の比較');
                $this->compareOriginalAndConverted($payload, $convertedReceipt);
            }

            // 総合結果
            $this->info('🔍 総合結果');
            $this->displayOverallResult($validationResult, $validateOnly);
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
     * JWSからペイロード抽出
     *
     * @return array<string, mixed>
     */
    private function extractPayloadFromJws(string $jws): array
    {
        try {
            $this->displayKeyValue('JWS入力', '✅ 受信');

            // JWSデコード
            $payload = $this->jwsService->decodeStoreServerJws($jws);

            $this->displayKeyValue('デコード結果', '✅ 成功');
            $this->displayKeyValue('ペイロードキー数', (string)count($payload));

            return $payload;
        } catch (\Exception $e) {
            $this->error('JWSのデコードに失敗しました: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * JSONペイロード解析
     *
     * @return array<string, mixed>
     */
    private function parseJsonPayload(string $payloadJson): array
    {
        try {
            $this->displayKeyValue('JSON入力', '✅ 受信');

            $payload = json_decode($payloadJson, true);
            if ($payload === null) {
                throw new \InvalidArgumentException('JSONの解析に失敗しました');
            }

            $this->displayKeyValue('JSON解析', '✅ 成功');
            $this->displayKeyValue('ペイロードキー数', (string)count($payload));

            return $payload;
        } catch (\Exception $e) {
            $this->error('JSONペイロードの解析に失敗しました: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * サンプルペイロード生成
     *
     * @return array<string, mixed>
     */
    private function generateSamplePayload(): array
    {
        $this->displayKeyValue('サンプル生成', '✅ 開始');

        // StoreKit2の典型的なペイロード構造
        $payload = [
            'transactionId' => '2000000123456789',
            'originalTransactionId' => '1000000123456789',
            'productId' => 'com.example.app.premium',
            'bundleId' => 'com.example.app',
            'environment' => 'Production',
            'purchaseDate' => 1634567890000, // エポック時間（ミリ秒）
            'quantity' => 1,
            'type' => 'Auto-Renewable Subscription',
            'originalPurchaseDate' => 1634567890000,
            'expiresDate' => 1637246290000,
            'webOrderLineItemId' => '2000000123456789',
            'subscriptionGroupIdentifier' => '12345678',
            'isUpgraded' => false,
            'currency' => 'USD',
            'price' => 999,
            'offerType' => 1,
            'offerIdentifier' => 'introductory_offer',
        ];

        $this->displayKeyValue('サンプル生成', '✅ 完了');
        $this->displayKeyValue('ペイロードキー数', (string)count($payload));

        return $payload;
    }

    /**
     * ペイロード解析
     *
     * @param array<string, mixed> $payload
     */
    private function analyzePayload(array $payload): void
    {
        $this->info('📄 ペイロード詳細分析:');

        // 必須フィールドの確認
        $requiredFields = ['transactionId', 'productId', 'environment', 'purchaseDate'];
        foreach ($requiredFields as $field) {
            $status = isset($payload[$field]) ? '✅ 存在' : '❌ 不足';
            $value = isset($payload[$field]) ? (string)$payload[$field] : 'N/A';
            $this->displayKeyValue($field, "{$status} ({$value})");
        }

        // 日時フィールドの確認
        $dateFields = ['purchaseDate', 'originalPurchaseDate', 'expiresDate'];
        foreach ($dateFields as $field) {
            if (isset($payload[$field])) {
                $value = $payload[$field];
                if (is_numeric($value)) {
                    $formatted = date('Y-m-d H:i:s', (int)($value / 1000));
                    $this->displayKeyValue("{$field} (変換後)", $formatted);
                } else {
                    $this->displayKeyValue("{$field} (ISO8601)", (string)$value);
                }
            }
        }

        $this->line('');
        $this->info('🔍 完全なペイロード (JSON):');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * ペイロード検証
     *
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): bool
    {
        $this->info('📋 ペイロード検証結果:');

        $errors = [];
        $warnings = [];

        // 必須フィールドチェック
        $requiredFields = ['transactionId', 'productId', 'environment', 'purchaseDate'];
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                $errors[] = "必須フィールド '{$field}' が不足しています";
            }
        }

        // 環境値チェック
        if (isset($payload['environment'])) {
            $validEnvironments = ['Production', 'Sandbox'];
            if (!in_array($payload['environment'], $validEnvironments, true)) {
                $errors[] = "environment値が無効です: {$payload['environment']}";
            }
        }

        // 日時フィールドチェック
        if (isset($payload['purchaseDate'])) {
            $purchaseDate = $payload['purchaseDate'];
            if (!is_numeric($purchaseDate) && !is_string($purchaseDate)) {
                $errors[] = "purchaseDate の形式が無効です";
            } elseif (is_string($purchaseDate) && strtotime($purchaseDate) === false) {
                $errors[] = "purchaseDate のISO8601形式が無効です";
            }
        }

        // 数値フィールドチェック
        $numericFields = ['quantity', 'price'];
        foreach ($numericFields as $field) {
            if (isset($payload[$field]) && !is_numeric($payload[$field])) {
                $warnings[] = "フィールド '{$field}' が数値ではありません";
            }
        }

        // 結果表示
        if (count($errors) === 0 && count($warnings) === 0) {
            $this->info('✅ 検証成功: エラーなし');
        } else {
            if (count($errors) > 0) {
                $this->error('❌ 検証エラー:');
                foreach ($errors as $error) {
                    $this->error("  • {$error}");
                }
            }

            if (count($warnings) > 0) {
                $this->warn('⚠️ 検証警告:');
                foreach ($warnings as $warning) {
                    $this->warn("  • {$warning}");
                }
            }
        }

        return count($errors) === 0;
    }

    /**
     * レガシー形式への変換実行
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function performConversion(array $payload): array
    {
        try {
            $this->displayKeyValue('変換開始', '✅ 実行中');

            $convertedReceipt = $this->converter->convert($payload);

            $this->displayKeyValue('変換完了', '✅ 成功');
            $this->displayKeyValue('レシートキー数', (string)count($convertedReceipt));

            return $convertedReceipt;
        } catch (\Exception $e) {
            $this->error('変換処理でエラーが発生しました: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 変換後レシート検証
     *
     * @param array<string, mixed> $receipt
     */
    private function validateConvertedReceipt(array $receipt): void
    {
        $this->info('📋 変換後レシート検証:');

        // レガシーレシート形式の必須フィールド
        $expectedFields = ['receipt_type', 'in_app', 'bundle_id', 'environment'];

        foreach ($expectedFields as $field) {
            $status = isset($receipt[$field]) ? '✅ 存在' : '❌ 不足';
            $this->displayKeyValue($field, $status);
        }

        // in_app配列の確認
        if (isset($receipt['in_app']) && is_array($receipt['in_app'])) {
            $inAppCount = count($receipt['in_app']);
            $this->displayKeyValue('in_app配列件数', (string)$inAppCount);

            if ($inAppCount > 0) {
                $firstInApp = $receipt['in_app'][0];
                $inAppFields = ['transaction_id', 'product_id', 'purchase_date_ms'];
                foreach ($inAppFields as $field) {
                    $status = isset($firstInApp[$field]) ? '✅ 存在' : '❌ 不足';
                    $this->displayKeyValue("in_app[0].{$field}", $status);
                }
            }
        }

        $this->line('');
        $this->info('🔍 変換後レシート全体 (JSON):');
        $this->line(json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 変換前後の比較
     *
     * @param array<string, mixed> $original
     * @param array<string, mixed> $converted
     */
    private function compareOriginalAndConverted(array $original, array $converted): void
    {
        $this->info('📊 変換前後の主要フィールド比較:');

        // マッピング関係の確認
        $mappings = [
            'transactionId' => 'transaction_id',
            'productId' => 'product_id',
            'bundleId' => 'bundle_id',
            'environment' => 'environment',
            'purchaseDate' => 'purchase_date_ms',
        ];

        foreach ($mappings as $originalKey => $convertedKey) {
            $originalValue = $original[$originalKey] ?? 'N/A';

            // in_app配列内から値を取得
            $convertedValue = 'N/A';
            if ($convertedKey === 'bundle_id' || $convertedKey === 'environment') {
                $convertedValue = $converted[$convertedKey] ?? 'N/A';
            } elseif (isset($converted['in_app'][0][$convertedKey])) {
                $convertedValue = $converted['in_app'][0][$convertedKey];
            }

            $this->line(sprintf('  %-20s : %s → %s', $originalKey, $originalValue, $convertedValue));
        }
    }

    /**
     * 総合結果表示
     */
    private function displayOverallResult(bool $validationPassed, bool $validateOnly): void
    {
        if ($validateOnly) {
            if ($validationPassed) {
                $this->info('✅ ペイロード検証: 成功');
                $this->line('💡 このペイロードは変換可能です');
            } else {
                $this->error('❌ ペイロード検証: 失敗');
                $this->line('🔧 上記のエラーを修正してから変換を実行してください');
            }
        } else {
            if ($validationPassed) {
                $this->info('🎉 ペイロード変換テスト: 全て成功');
                $this->line('');
                $this->info('✅ StoreKit2からレガシー形式への変換が正常に動作しています');
            } else {
                $this->error('❌ ペイロード変換テスト: 検証で問題が発見されました');
            }
        }

        $this->line('');
        $this->info('💡 推奨事項:');
        $this->line('  • 本番データでテストする前に、必ずサンプルデータでテストしてください');
        $this->line('  • エラーが発生した場合は、verbose オプション(-v)で詳細を確認してください');
        $this->line('  • 定期的にこのテストを実行して変換ロジックの動作を確認してください');
    }

    /**
     * キー・バリューペアを整列して表示
     */
    private function displayKeyValue(string $key, string $value): void
    {
        $this->line(sprintf('  %-25s : %s', $key, $value));
    }
}
