<?php

declare(strict_types=1);

namespace App\Console\Commands\AppStore;

use Illuminate\Console\Command;

/**
 * StoreKit2設定確認デバッグコマンド
 * App Store Connect API、証明書、バンドルID等の設定状況を確認
 */
class DebugConfigCommand extends Command
{
    /**
     * コマンド名
     *
     * @var string
     */
    protected $signature = 'appstore:debug-config 
                           {--environment=production : 環境 (production|sandbox)}
                           {--show-secrets : 秘密情報の一部を表示（デバッグ用）}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = 'StoreKit2関連の設定値を確認・検証';

    /**
     * コマンド実行
     */
    public function handle(): int
    {
        $environment = $this->option('environment');
        $showSecrets = $this->option('show-secrets');

        // 環境値の検証
        if (!in_array($environment, ['production', 'sandbox'], true)) {
            $this->error('環境は production または sandbox を指定してください');
            return self::FAILURE;
        }

        $this->info("=== StoreKit2 設定確認 ===");
        $this->info("対象環境: {$environment}");
        $this->line('');

        $hasErrors = false;

        // App Store Connect API設定
        $this->info('📋 App Store Connect API 設定');
        $hasErrors |= $this->checkAppStoreConnectConfig($showSecrets);
        $this->line('');

        // バンドルID設定
        $this->info('📱 バンドルID 設定');
        $hasErrors |= $this->checkBundleIdConfig($environment);
        $this->line('');

        // 証明書ディレクトリ設定
        $this->info('🔐 証明書管理 設定');
        $hasErrors |= $this->checkCertificateConfig();
        $this->line('');

        // 通貨設定
        $this->info('💰 通貨・ストア 設定');
        $hasErrors |= $this->checkCurrencyConfig();
        $this->line('');

        // 結果サマリー
        if ($hasErrors) {
            $this->error('❌ 設定に問題が見つかりました。上記のエラーを修正してください。');
            return self::FAILURE;
        } else {
            $this->info('✅ すべての設定が正常です。');
            return self::SUCCESS;
        }
    }

    /**
     * App Store Connect API設定をチェック
     */
    private function checkAppStoreConnectConfig(bool $showSecrets): bool
    {
        $hasError = false;

        // Issuer ID
        $issuer = config('wp_currency.store.app_store.storekit2.issuer');
        if ($issuer === null || $issuer === '') {
            $this->error('  ❌ Issuer ID が設定されていません (wp_currency.store.app_store.storekit2.issuer)');
            $hasError = true;
        } else {
            $displayIssuer = $showSecrets ? $issuer : $this->maskSecret($issuer);
            $this->info("  ✅ Issuer ID: {$displayIssuer}");
        }

        // Key ID
        $keyId = config('wp_currency.store.app_store.storekit2.key_id');
        if ($keyId === null || $keyId === '') {
            $this->error('  ❌ Key ID が設定されていません (wp_currency.store.app_store.storekit2.key_id)');
            $hasError = true;
        } else {
            $displayKeyId = $showSecrets ? $keyId : $this->maskSecret($keyId);
            $this->info("  ✅ Key ID: {$displayKeyId}");
        }

        // Private Key
        $privateKey = config('wp_currency.store.app_store.storekit2.private_key');
        if ($privateKey === null || $privateKey === '') {
            $this->error('  ❌ Private Key が設定されていません (wp_currency.store.app_store.storekit2.private_key)');
            $hasError = true;
        } else {
            if ($showSecrets) {
                $keyLength = strlen($privateKey);
                $this->info("  ✅ Private Key: 設定済み ({$keyLength} 文字)");

                // Private Keyの形式チェック
                if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false) {
                    $this->info('    📝 形式: PEM形式');
                } else {
                    $this->warn('    ⚠️ 注意: PEM形式でない可能性があります');
                }
            } else {
                $this->info('  ✅ Private Key: 設定済み');
            }
        }

        return $hasError;
    }

    /**
     * バンドルID設定をチェック
     */
    private function checkBundleIdConfig(string $environment): bool
    {
        $hasError = false;

        if ($environment === 'production') {
            $bundleId = config('wp_currency.store.app_store.production_bundle_id');
            if ($bundleId === null || $bundleId === '') {
                $this->error('  ❌ 本番用バンドルID が設定されていません (wp_currency.store.app_store.production_bundle_id)');
                $hasError = true;
            } else {
                $this->info("  ✅ 本番用バンドルID: {$bundleId}");
            }
        } else {
            $bundleId = config('wp_currency.store.app_store.sandbox_bundle_id');
            if ($bundleId === null || $bundleId === '') {
                $this->error('  ❌ サンドボックス用バンドルID が設定されていません (wp_currency.store.app_store.sandbox_bundle_id)');
                $hasError = true;
            } else {
                $this->info("  ✅ サンドボックス用バンドルID: {$bundleId}");
            }
        }

        return $hasError;
    }

    /**
     * 証明書設定をチェック
     */
    private function checkCertificateConfig(): bool
    {
        $hasError = false;

        $certDir = config('wp_currency.store.app_store.storekit2.cert_dir');
        if ($certDir === null || $certDir === '') {
            $this->error('  ❌ 証明書ディレクトリが設定されていません (wp_currency.store.app_store.storekit2.cert_dir)');
            $hasError = true;
        } else {
            $this->info("  ✅ 証明書ディレクトリ: {$certDir}");

            // ディレクトリの存在チェック
            if (!is_dir($certDir)) {
                $this->warn("  ⚠️ ディレクトリが存在しません: {$certDir}");
                $this->info('    💡 初回実行時に自動作成されます');
            } else {
                $this->info('  ✅ ディレクトリ存在確認: OK');

                // 書き込み権限チェック
                if (!is_writable($certDir)) {
                    $this->error("  ❌ ディレクトリに書き込み権限がありません: {$certDir}");
                    $hasError = true;
                } else {
                    $this->info('  ✅ 書き込み権限: OK');
                }

                // 既存証明書ファイルの確認
                $this->checkExistingCertificates($certDir);
            }
        }

        return $hasError;
    }

    /**
     * 通貨・ストア設定をチェック
     */
    private function checkCurrencyConfig(): bool
    {
        $hasError = false;

        // ストア設定の存在確認
        $storeConfig = config('wp_currency.store');
        if ($storeConfig === null || (is_array($storeConfig) && count($storeConfig) === 0)) {
            $this->error('  ❌ ストア設定が見つかりません (wp_currency.store)');
            $hasError = true;
        } else {
            $this->info('  ✅ ストア設定: 存在');
        }

        // App Store設定の存在確認
        $appStoreConfig = config('wp_currency.store.app_store');
        if ($appStoreConfig === null || (is_array($appStoreConfig) && count($appStoreConfig) === 0)) {
            $this->error('  ❌ App Store設定が見つかりません (wp_currency.store.app_store)');
            $hasError = true;
        } else {
            $this->info('  ✅ App Store設定: 存在');
        }

        return $hasError;
    }

    /**
     * 既存の証明書ファイルを確認
     */
    private function checkExistingCertificates(string $certDir): void
    {
        $certificates = [
            'apple-root-ca-g2.pem' => 'Apple Root CA G2',
            'apple-root-ca-g3.pem' => 'Apple Root CA G3',
        ];

        foreach ($certificates as $filename => $description) {
            $filePath = $certDir . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filePath)) {
                $fileSize = filesize($filePath);
                $fileModTime = filemtime($filePath);
                $modifiedTime = $fileModTime !== false ? date('Y-m-d H:i:s', $fileModTime) : 'N/A';
                $this->info("    📄 {$description}: 存在 ({$fileSize} bytes, 更新: {$modifiedTime})");

                // 証明書の有効性チェック（基本的な形式確認）
                $content = file_get_contents($filePath);
                if ($content !== false && strpos($content, '-----BEGIN CERTIFICATE-----') !== false) {
                    $this->info("      ✅ PEM形式: OK");
                } else {
                    $this->warn("      ⚠️ PEM形式ではありません");
                }
            } else {
                $this->warn("    ⚠️ {$description}: 未ダウンロード");
                $this->info('      💡 初回API呼び出し時に自動ダウンロードされます');
            }
        }
    }

    /**
     * 秘密情報をマスク
     */
    private function maskSecret(string $secret): string
    {
        if (strlen($secret) <= 8) {
            return str_repeat('*', strlen($secret));
        }

        $start = substr($secret, 0, 4);
        $end = substr($secret, -4);
        $middle = str_repeat('*', strlen($secret) - 8);

        return $start . $middle . $end;
    }
}
