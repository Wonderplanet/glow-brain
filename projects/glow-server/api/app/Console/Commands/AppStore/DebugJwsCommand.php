<?php

declare(strict_types=1);

namespace App\Console\Commands\AppStore;

use Illuminate\Console\Command;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\CertificateManager;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\JwsService;

/**
 * JWS（JSON Web Signature）デバッグコマンド
 * StoreKit2のJWSトークンをデコード・検証し、詳細情報を表示
 */
class DebugJwsCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'appstore:debug-jws 
                           {jws : JWSトークン（signedTransactionまたはother JWS）} 
                           {--type=transaction : JWSタイプ (transaction|notification|other)}
                           {--skip-verify : 署名検証をスキップ}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = 'StoreKit2 JWSトークンをデバッグ・解析';

    public function __construct(
        private JwsService $jwsService,
        private CertificateManager $certificateManager
    ) {
        parent::__construct();
    }

    /**
     * コマンド実行
     */
    public function handle(): int
    {
        $jws = $this->argument('jws');
        $type = $this->option('type') ?? 'transaction';
        $skipVerify = $this->option('skip-verify');

        $this->info("=== StoreKit2 JWS デバッグ・解析 ===");
        $this->info("JWSタイプ: {$type}");
        $this->info("署名検証: " . ($skipVerify ? '❌ スキップ' : '✅ 実行'));
        $this->line('');

        try {
            // 1. JWS構造の基本チェック
            $this->info('🔍 Step 1: JWS構造の基本チェック');
            $this->analyzeJwsStructure($jws);
            $this->line('');

            // 2. ヘッダー解析
            $this->info('🔍 Step 2: JWSヘッダー解析');
            $header = $this->analyzeJwsHeader($jws);
            $this->line('');

            // 3. ペイロード解析
            $this->info('🔍 Step 3: JWSペイロード解析');
            $payload = $this->analyzeJwsPayload($jws, $type);
            $this->line('');

            // 4. 証明書チェーン検証（署名検証をスキップしない場合）
            if (!$skipVerify) {
                $this->info('🔍 Step 4: 証明書チェーン検証');
                $this->analyzeCertificateChain($header);
                $this->line('');

                // 5. 署名検証
                $this->info('🔍 Step 5: JWS署名検証');
                $this->verifyJwsSignature($jws, $type);
                $this->line('');
            }

            // 6. 総合診断結果
            $this->info('🔍 Step 6: 総合診断結果');
            $this->displayDiagnosisResult($payload, $header, $skipVerify);
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
     * JWS構造の基本チェック
     */
    private function analyzeJwsStructure(string $jws): void
    {
        $parts = explode('.', $jws);
        $partsCount = count($parts);

        $this->displayKeyValue('JWS形式', $partsCount === 3 ? '✅ 正常（3部分）' : "❌ 異常（{$partsCount}部分）");

        // ヘッダー部分は必ず存在する
        $this->displayKeyValue('ヘッダー長', strlen($parts[0]) . ' 文字');

        if ($partsCount >= 2) {
            $this->displayKeyValue('ペイロード長', strlen($parts[1]) . ' 文字');
        }
        if ($partsCount >= 3) {
            $this->displayKeyValue('署名長', strlen($parts[2]) . ' 文字');
        }

        if ($partsCount !== 3) {
            throw new \InvalidArgumentException('JWSの形式が正しくありません。ヘッダー.ペイロード.署名の3部分である必要があります。');
        }
    }

    /**
     * JWSヘッダー解析
     *
     * @return array<string, mixed>
     */
    private function analyzeJwsHeader(string $jws): array
    {
        $parts = explode('.', $jws);
        $headerJson = base64_decode($parts[0], true);

        if ($headerJson === false || $headerJson === '') {
            throw new \InvalidArgumentException('JWSヘッダーのBase64デコードに失敗しました');
        }

        $header = json_decode($headerJson, true);
        if ($header === null) {
            throw new \InvalidArgumentException('JWSヘッダーのJSON解析に失敗しました');
        }

        $this->displayKeyValue('アルゴリズム', $header['alg'] ?? 'N/A');
        $this->displayKeyValue('証明書チェーン件数', isset($header['x5c']) ? (string)count($header['x5c']) : 'N/A');
        $this->displayKeyValue('キーID', $header['kid'] ?? 'N/A');
        $this->displayKeyValue('タイプ', $header['typ'] ?? 'N/A');

        $this->line('');
        $this->info('🔍 完全なヘッダー情報 (JSON):');
        $headerJson = json_encode($header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->line($headerJson ?: '{}');

        return $header;
    }

    /**
     * JWSペイロード解析
     *
     * @return array<string, mixed>
     */
    private function analyzeJwsPayload(string $jws, string $type): array
    {
        $parts = explode('.', $jws);
        $payloadJson = base64_decode($parts[1], true);

        if ($payloadJson === false || $payloadJson === '') {
            throw new \InvalidArgumentException('JWSペイロードのBase64デコードに失敗しました');
        }

        $payload = json_decode($payloadJson, true);
        if ($payload === null) {
            throw new \InvalidArgumentException('JWSペイロードのJSON解析に失敗しました');
        }

        // タイプ別の主要フィールド表示
        if ($type === 'transaction') {
            $this->analyzeTransactionPayload($payload);
        } elseif ($type === 'notification') {
            $this->analyzeNotificationPayload($payload);
        } else {
            $this->analyzeGenericPayload($payload);
        }

        $this->line('');
        $this->info('🔍 完全なペイロード情報 (JSON):');
        $payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->line($payloadJson ?: '{}');

        return $payload;
    }

    /**
     * トランザクションペイロード解析
     *
     * @param array<string, mixed> $payload
     */
    private function analyzeTransactionPayload(array $payload): void
    {
        $this->info('📄 トランザクション固有情報:');
        $this->displayKeyValue('トランザクションID', $payload['transactionId'] ?? 'N/A');
        $this->displayKeyValue('オリジナルトランザクションID', $payload['originalTransactionId'] ?? 'N/A');
        $this->displayKeyValue('プロダクトID', $payload['productId'] ?? 'N/A');
        $this->displayKeyValue('バンドルID', $payload['bundleId'] ?? 'N/A');
        $this->displayKeyValue('環境', $payload['environment'] ?? 'N/A');

        // 購入日時の表示
        if (isset($payload['purchaseDate'])) {
            $purchaseDate = $payload['purchaseDate'];
            if (is_numeric($purchaseDate)) {
                $formatted = date('Y-m-d H:i:s', (int)($purchaseDate / 1000)) . ' (エポック: ' . $purchaseDate . ')';
            } else {
                $formatted = $purchaseDate;
            }
            $this->displayKeyValue('購入日時', $formatted);
        }

        $this->displayKeyValue('数量', isset($payload['quantity']) ? (string)$payload['quantity'] : 'N/A');
        $this->displayKeyValue('タイプ', $payload['type'] ?? 'N/A');
    }

    /**
     * 通知ペイロード解析
     *
     * @param array<string, mixed> $payload
     */
    private function analyzeNotificationPayload(array $payload): void
    {
        $this->info('📄 通知固有情報:');
        $this->displayKeyValue('通知タイプ', $payload['notificationType'] ?? 'N/A');
        $this->displayKeyValue('通知サブタイプ', $payload['subtype'] ?? 'N/A');
        $this->displayKeyValue('通知UUID', $payload['notificationUUID'] ?? 'N/A');
        $this->displayKeyValue('バージョン', $payload['version'] ?? 'N/A');
    }

    /**
     * 汎用ペイロード解析
     *
     * @param array<string, mixed> $payload
     */
    private function analyzeGenericPayload(array $payload): void
    {
        $this->info('📄 汎用ペイロード情報:');
        foreach (['iss', 'iat', 'exp', 'aud', 'sub'] as $standardClaim) {
            if (isset($payload[$standardClaim])) {
                $value = $payload[$standardClaim];
                if (in_array($standardClaim, ['iat', 'exp']) && is_numeric($value)) {
                    $value = date('Y-m-d H:i:s', $value) . ' (エポック: ' . $value . ')';
                }
                $this->displayKeyValue($standardClaim, $value);
            }
        }
    }

    /**
     * 証明書チェーン検証
     *
     * @param array<string, mixed> $header
     */
    private function analyzeCertificateChain(array $header): void
    {
        if (!isset($header['x5c']) || !is_array($header['x5c'])) {
            $this->warn('⚠️ 証明書チェーン（x5c）が見つかりません');
            return;
        }

        $certificates = $header['x5c'];
        $this->displayKeyValue('証明書数', (string)count($certificates));

        foreach ($certificates as $index => $certData) {
            $certNumber = $index + 1;
            $this->info("証明書 #{$certNumber}:");

            try {
                $cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($certData, 64) . "-----END CERTIFICATE-----";
                $parsed = openssl_x509_parse($cert);

                if ($parsed !== false) {
                    $this->displayKeyValue('  サブジェクト', $parsed['subject']['CN'] ?? 'N/A');
                    $this->displayKeyValue('  発行者', $parsed['issuer']['CN'] ?? 'N/A');
                    $this->displayKeyValue('  有効期限', date('Y-m-d H:i:s', $parsed['validTo_time_t']));
                    $this->displayKeyValue('  シリアル番号', $parsed['serialNumber'] ?? 'N/A');
                } else {
                    $this->warn("  ⚠️ 証明書の解析に失敗しました");
                }
            } catch (\Exception $e) {
                $this->warn("  ⚠️ 証明書の処理中にエラー: " . $e->getMessage());
            }
        }

        // CertificateManagerを使用した追加検証
        $managerValidation = $this->validateCertificateChainWithManager($certificates);
        $this->displayKeyValue('CertificateManager検証', $managerValidation ? '✅ 成功' : '❌ 失敗');
    }

    /**
     * JWS署名検証
     */
    private function verifyJwsSignature(string $jws, string $type): void
    {
        try {
            if ($type === 'transaction') {
                $result = $this->jwsService->decodeStoreServerJws($jws);
                $this->info('✅ 署名検証: 成功');
                $this->displayKeyValue('デコード結果', '正常にデコードされました');
            } else {
                // 他のタイプの場合は基本的な検証のみ
                $this->info('⚠️ トランザクション以外のJWSは基本検証のみ実行');
                // ここで必要に応じて他のタイプの検証ロジックを追加
            }
        } catch (\Exception $e) {
            $this->error('❌ 署名検証: 失敗');
            $this->error('エラー詳細: ' . $e->getMessage());
        }
    }

    /**
     * 総合診断結果表示
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $header
     */
    private function displayDiagnosisResult(array $payload, array $header, bool $skipVerify): void
    {
        $this->info('📋 総合診断結果:');

        // 基本構造チェック
        $this->displayKeyValue('JWS構造', '✅ 正常');
        $this->displayKeyValue('ヘッダー解析', '✅ 成功');
        $this->displayKeyValue('ペイロード解析', '✅ 成功');

        // 証明書チェック
        $hasCerts = isset($header['x5c']) && is_array($header['x5c']);
        $this->displayKeyValue('証明書チェーン', $hasCerts ? '✅ 存在' : '❌ 未検出');

        // 署名検証結果
        if ($skipVerify) {
            $this->displayKeyValue('署名検証', '⏭️ スキップ');
        } else {
            $this->displayKeyValue('署名検証', '✅ 実行済み（詳細は上記参照）');
        }

        // 有効期限チェック（存在する場合）
        if (isset($payload['exp'])) {
            $isExpired = time() > $payload['exp'];
            $this->displayKeyValue('有効期限', $isExpired ? '❌ 期限切れ' : '✅ 有効');
        }

        $this->line('');
        $this->info('💡 推奨事項:');
        if ($skipVerify) {
            $this->line('  • 署名検証をスキップしています。本番環境では必ず検証してください。');
        }
        if (!$hasCerts) {
            $this->line('  • 証明書チェーンが見つかりません。JWSの信頼性を確認してください。');
        }
        $this->line('  • 詳細なログが必要な場合は -v オプションを使用してください。');
    }

    /**
     * キー・バリューペアを整列して表示
     */
    private function displayKeyValue(string $key, string $value): void
    {
        $this->line(sprintf('  %-25s : %s', $key, $value));
    }

    /**
     * CertificateManagerを使用した証明書チェーン検証
     *
     * @param array<string> $certificates
     */
    private function validateCertificateChainWithManager(array $certificates): bool
    {
        try {
            if (count($certificates) === 0) {
                return false;
            }

            // CertificateManagerから実際のApple Root CA証明書を取得
            $appleRootCAs = $this->certificateManager->getAllAppleRootCaPems();

            if (count($appleRootCAs) === 0) {
                $this->warn('Apple Root CA証明書の取得に失敗しました');
                return false;
            }

            // 証明書チェーンをPEM形式に変換
            $pemCertificates = [];
            foreach ($certificates as $certData) {
                $pemCertificates[] = "-----BEGIN CERTIFICATE-----\n"
                    . chunk_split($certData, 64)
                    . "-----END CERTIFICATE-----";
            }

            // 各Apple Root CAとの検証を試行
            foreach ($appleRootCAs as $index => $rootCA) {
                if ($this->verifyChainWithRoot($pemCertificates, $rootCA)) {
                    $this->info("  ✅ Apple Root CA #" . ($index + 1) . " で証明書チェーン検証が成功しました");
                    return true;
                }
            }

            $this->warn('  ⚠️ いずれのApple Root CAでも証明書チェーン検証に失敗しました');
            return false;
        } catch (\Exception $e) {
            $this->error('証明書チェーン検証エラー: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 指定されたRoot CAで証明書チェーンを検証
     *
     * @param array<string> $pemCertificates
     * @param string $rootCA
     */
    private function verifyChainWithRoot(array $pemCertificates, string $rootCA): bool
    {
        try {
            // 証明書チェーン + ルートCA を一時ファイルに書き込み
            $fullChain = array_merge($pemCertificates, [$rootCA]);
            $chainPem = implode("\n", $fullChain);
            $chainFile = tempnam(sys_get_temp_dir(), 'jws_chain_');

            if ($chainFile === false) {
                return false;
            }

            file_put_contents($chainFile, $chainPem);

            // リーフ証明書で検証
            $leafCert = $pemCertificates[0];
            $res = openssl_x509_read($leafCert);

            if ($res === false) {
                @unlink($chainFile);
                return false;
            }

            // 証明書チェーンの検証
            $isValid = openssl_x509_checkpurpose($res, X509_PURPOSE_ANY, [$chainFile]);
            @unlink($chainFile);

            return $isValid === true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
