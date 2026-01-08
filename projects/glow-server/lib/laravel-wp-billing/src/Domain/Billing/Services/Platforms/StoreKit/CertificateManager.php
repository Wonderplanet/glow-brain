<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use Carbon\Carbon;
use Exception;
use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;

/**
 * Apple証明書の動的管理サービス
 *
 * 機能:
 * - 証明書の自動ダウンロード
 * - 有効期限チェック
 * - ローカルキャッシュ管理
 * - 古い証明書のバックアップ
 */
class CertificateManager
{
    private const APPLE_ROOT_CA_G2_URL = 'https://www.apple.com/certificateauthority/AppleRootCA-G2.cer';
    private const APPLE_ROOT_CA_G3_URL = 'https://www.apple.com/certificateauthority/AppleRootCA-G3.cer';
    private const CERT_FILENAMES = [
        'g2' => 'apple-root-ca-g2.pem',
        'g3' => 'apple-root-ca-g3.pem',
    ];
    private const CACHE_DURATION_HOURS = 24; // 24時間キャッシュ

    private string $certDir;
    /** @var array<string, string> */
    private array $cachedCertPem = [];
    /** @var array<string, Carbon> */
    private array $cacheTimestamp = [];

    public function __construct(?string $certDir = null)
    {
        // 証明書保存ディレクトリの設定
        $this->certDir = $certDir ?? $this->getDefaultCertDir();
        $this->ensureCertDirExists();
    }

    /**
     * Apple Root CA証明書をPEM形式で取得（g2/g3指定必須）
     *
     * @param string $key 'g2' or 'g3'
     * @return string PEM形式の証明書
     */
    public function getAppleRootCaPem(string $key): string
    {
        // メモリキャッシュをチェック（keyごとにキャッシュ）
        if (isset($this->cachedCertPem[$key]) && isset($this->cacheTimestamp[$key])) {
            $now = Carbon::now();
            if ($this->cacheTimestamp[$key]->diffInHours($now) < self::CACHE_DURATION_HOURS) {
                return $this->cachedCertPem[$key];
            }
        }

        // ローカルファイルをチェック
        $certPath = $this->getCertPathByKey($key);
        if (file_exists($certPath)) {
            $certPem = file_get_contents($certPath);
            if ($certPem !== false) {
                // 証明書の有効期限をチェック
                if ($this->isCertificateValid($certPem)) {
                    $this->updateCache($key, $certPem);
                    return $certPem;
                } else {
                    return $this->downloadAndSaveCertificate($key);
                }
            }
        }

        // ファイルが存在しないかロードに失敗した場合
        return $this->downloadAndSaveCertificate($key);
    }

    /**
     * 新しい証明書をダウンロードして保存（key必須）
     *
     * @param string $key 'g2' or 'g3'
     * @return string PEM形式の証明書
     * @throws WpBillingException
     */
    private function downloadAndSaveCertificate(string $key): string
    {
        $url = $key === 'g2' ? self::APPLE_ROOT_CA_G2_URL : self::APPLE_ROOT_CA_G3_URL;
        try {
            $derData = $this->downloadCertificateByUrl($url);
            $pemData = $this->convertDerToPem($derData);
            if (!$this->isCertificateValid($pemData)) {
                throw new WpBillingException(
                    'Downloaded certificate is invalid or expired',
                    ErrorCode::INVALID_RECEIPT
                );
            }
            $this->backupOldCertificateByKey($key);
            $this->saveCertificateByKey($key, $pemData);
            $this->updateCache($key, $pemData);
            return $pemData;
        } catch (Exception $e) {
            $fallbackCert = $this->getFallbackCertificateByKey($key);
            if ($fallbackCert) {
                return $fallbackCert;
            }
            throw new WpBillingException("Failed to obtain Apple Root CA {$key} certificate: " . $e->getMessage());
        }
    }

    /**
     * キャッシュを更新（keyごと）
     *
     * @param string $key
     * @param string $pemData
     */
    private function updateCache(string $key, string $pemData): void
    {
        $this->cachedCertPem[$key] = $pemData;
        $this->cacheTimestamp[$key] = Carbon::now();
    }

    /**
     * キャッシュをクリア（テスト用、key指定可）
     */
    public function clearCache(?string $key = null): void
    {
        if ($key === null) {
            $this->cachedCertPem = [];
            $this->cacheTimestamp = [];
        } else {
            unset($this->cachedCertPem[$key], $this->cacheTimestamp[$key]);
        }
    }

    /**
     * 証明書ファイルを削除（テスト用、key指定必須）
     */
    public function deleteCertificateFile(string $key): void
    {
        $certPath = $this->getCertPathByKey($key);
        if (file_exists($certPath)) {
            unlink($certPath);
        }
    }

    /**
     * G2/G3両方のApple Root CA PEMを配列で返す（なければDL＆キャッシュ）
     *
     * @return string[]
     */
    public function getAllAppleRootCaPems(): array
    {
        $result = [];
        foreach (
            [
            'g2',
            'g3',
            ] as $key
        ) {
            $pem = $this->getAppleRootCaPem($key);
            if ($pem) {
                $result[] = $pem;
            }
        }
        return $result;
    }

    /**
     * 指定キーに対応する証明書ファイルのパスを取得
     *
     * @param string $key 'g2' or 'g3'
     * @return string 証明書ファイルの完全パス
     */
    private function getCertPathByKey(string $key): string
    {
        return $this->certDir . DIRECTORY_SEPARATOR . (self::CERT_FILENAMES[$key] ?? '');
    }

    /**
     * 指定URLから証明書（DER形式）をダウンロード
     *
     * @param string $url Apple証明書のダウンロードURL
     * @return string DER形式の証明書データ
     * @throws WpBillingException ダウンロードに失敗した場合
     */
    private function downloadCertificateByUrl(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'StoreKit2-Certificate-Manager/1.0',
            ],
        ]);
        $derData = file_get_contents($url, false, $context);
        if ($derData === false) {
            throw new WpBillingException(
                'Failed to download certificate from Apple: ' . $url,
                ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR
            );
        }
        return $derData;
    }

    /**
     * 証明書を指定のパスに保存
     *
     * @param string $key 'g2' or 'g3'
     * @param string $pemData PEM形式の証明書データ
     * @throws WpBillingException
     */
    private function saveCertificateByKey(string $key, string $pemData): void
    {
        $certPath = $this->getCertPathByKey($key);
        $result = file_put_contents($certPath, $pemData, LOCK_EX);
        if ($result === false) {
            throw new WpBillingException(
                "Failed to save certificate to {$certPath}",
                ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR
            );
        }
        chmod($certPath, 0644);
    }

    /**
     * 古い証明書をバックアップ（key必須）
     *
     * @param string $key 'g2' or 'g3'
     */
    private function backupOldCertificateByKey(string $key): void
    {
        $certPath = $this->getCertPathByKey($key);
        if (file_exists($certPath)) {
            $backupPath = $certPath . '.' . date('Y-m-d_H-i-s') . '.backup';
            copy($certPath, $backupPath);
        }
    }

    /**
     * 最新のバックアップ証明書を取得（key必須）
     *
     * @param string $key 'g2' or 'g3'
     * @return string|null PEM形式の証明書データ、なければnull
     */
    private function getFallbackCertificateByKey(string $key): ?string
    {
        $backupFiles = glob($this->getCertPathByKey($key) . '.*.backup');
        if ($backupFiles === false || count($backupFiles) === 0) {
            return null;
        }

        rsort($backupFiles);
        $latestBackup = $backupFiles[0];
        $content = file_get_contents($latestBackup);
        if ($content !== false) {
            return $content;
        }

        return null;
    }

    /**
     * 証明書の有効期限をチェック
     *
     * @param string $pemData PEM形式の証明書データ
     * @return bool 有効ならtrue、無効または期限切れならfalse
     */
    private function isCertificateValid(string $pemData): bool
    {
        $cert = openssl_x509_read($pemData);
        if ($cert === false) {
            return false;
        }
        $certInfo = openssl_x509_parse($cert);
        if ($certInfo === false) {
            return false;
        }
        // 有効期限をチェック
        $validTo = $certInfo['validTo_time_t'] ?? 0;
        $now = time();
        // 証明書が期限切れかチェック
        if ($validTo <= $now) {
            return false;
        }
        return true;
    }

    /**
     * 証明書保存ディレクトリのデフォルトパスを取得
     *
     * @return string
     */
    private function getDefaultCertDir(): string
    {
        // configから取得を試行、fallbackとして相対パスを使用
        $configPath = config('wp_currency.store.app_store.storekit2.cert_dir');
        if ($configPath) {
            return $configPath;
        }
        // fallback: 現在の作業ディレクトリ配下のstorageディレクトリ
        return getcwd() . '/storage/app/certificates';
    }

    /**
     * 証明書保存ディレクトリが存在し、書き込み可能かを確認
     *
     * @throws WpBillingException
     */
    private function ensureCertDirExists(): void
    {
        // 既にディレクトリが存在する場合は早期リターン
        if (is_dir($this->certDir)) {
            if (!is_writable($this->certDir)) {
                throw new WpBillingException(
                    "Certificate directory is not writable: {$this->certDir}",
                    ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR,
                );
            }
            return;
        }

        // ディレクトリ作成を試行（競合状態を考慮）
        $created = @mkdir($this->certDir, 0777, true);

        // 作成が失敗した場合でも、ディレクトリが存在していればOK（他のプロセスが作成した可能性）
        if (!$created && !is_dir($this->certDir)) {
            throw new WpBillingException(
                "Failed to create certificate directory: {$this->certDir}",
                ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR,
            );
        }

        // 書き込み権限チェック
        if (!is_writable($this->certDir)) {
            throw new WpBillingException(
                "Certificate directory is not writable: {$this->certDir}",
                ErrorCode::EXTERNAL_API_COMMUNICATION_ERROR
            );
        }
    }

    /**
     * DER形式の証明書データをPEM形式に変換
     *
     * @param string $derData DER形式の証明書データ
     * @return string PEM形式の証明書データ
     */
    private function convertDerToPem(string $derData): string
    {
        $pem = chunk_split(base64_encode($derData), 64, "\n");
        return "-----BEGIN CERTIFICATE-----\n{$pem}-----END CERTIFICATE-----\n";
    }
}
