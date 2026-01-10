<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services\Platforms\StoreKit;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;

/**
 * StoreKit2 JWS (JSON Web Signature) 検証サービス
 * storekit2contextからライブラリ用に移植
 */
class JwsService
{
    private CertificateManager $certificateManager;

    public function __construct(?CertificateManager $certificateManager = null)
    {
        $this->certificateManager = $certificateManager ?? new CertificateManager();
    }

    /**
     * Store Server API向けのJWS処理
     * 環境に応じて適切な検証レベルで処理する
     *
     * @param string $jws Apple Store Server APIから取得したJWSトークン
     * @return array<string, mixed> デコードされたペイロードデータ
     * @throws WpBillingException JWS検証に失敗した場合
     */
    public function decodeStoreServerJws(string $jws): array
    {
        return $this->verify($jws);
    }

    /**
     * JWSの検証（主要メソッド）
     * Apple署名の検証を行い、信頼できるペイロードを返す
     *
     * @param string $jws 検証対象のJWSトークン
     * @return array<string, mixed> 検証済みペイロードデータ
     * @throws WpBillingException 証明書チェーンまたは署名検証に失敗した場合
     */
    public function verify(string $jws): array
    {
        $header = $this->parseJwsHeader($jws);
        $x5c = $header['x5c'];
        $certs = $this->convertX5cToPem($x5c);
        $rootCAs = $this->certificateManager->getAllAppleRootCaPems();
        $chainResult = $this->validateCertificateChain($certs, $rootCAs);
        $publicKey = openssl_pkey_get_public($chainResult['leafCert']);
        if (!$publicKey) {
            throw new WpBillingException('leaf証明書の公開鍵取得失敗', ErrorCode::APPSTORE_JWS_SIGNATURE_INVALID);
        }
        return $this->verifySignature($jws, $publicKey);
    }

    /**
     * JWSからペイロードのみを取得
     *
     * @param string $jws JWSトークン
     * @return array<string, mixed> ペイロードデータ
     * @throws WpBillingException JWS形式が不正な場合
     */
    public function decodePayloadOnly(string $jws): array
    {
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            throw new WpBillingException('JWS format error', ErrorCode::APPSTORE_JWS_FORMAT_INVALID);
        }
        $payload = json_decode(base64_decode($parts[1]), true);
        return $payload ?: [];
    }

    /**
     * JWSヘッダーをパースし、必要な情報を検証する
     * アルゴリズムがES256、x5c証明書チェーンが存在することを確認
     *
     * @param string $jws JWSトークン
     * @return array<string, mixed> パース済みヘッダー情報
     * @throws WpBillingException ヘッダー形式またはアルゴリズムが不正な場合
     */
    private function parseJwsHeader(string $jws): array
    {
        $parts = explode('.', $jws);
        if (count($parts) !== 3) {
            throw new WpBillingException('JWS format error', ErrorCode::APPSTORE_JWS_FORMAT_INVALID);
        }
        [$headerB64] = $parts;
        $headerJson = base64_decode(strtr($headerB64, '-_', '+/'));
        $header = json_decode($headerJson, true);
        if (!is_array($header)) {
            throw new WpBillingException('JWS header decode error', ErrorCode::APPSTORE_JWS_FORMAT_INVALID);
        }
        if (!isset($header['x5c']) || $header['x5c'] === [] || !is_array($header['x5c'])) {
            throw new WpBillingException('JWS x5c header missing', ErrorCode::APPSTORE_JWS_FORMAT_INVALID);
        }
        if (!isset($header['alg']) || $header['alg'] !== 'ES256') {
            throw new WpBillingException('JWS alg must be ES256', ErrorCode::APPSTORE_JWS_FORMAT_INVALID);
        }
        return $header;
    }

    /**
     * x5c証明書配列をPEM形式に変換
     * Base64エンコードされた証明書をPEMフォーマットに変換
     *
     * @param array<string> $x5c Base64エンコードされた証明書配列
     * @return array<string> PEM形式証明書配列
     */
    private function convertX5cToPem(array $x5c): array
    {
        $pems = [];
        foreach ($x5c as $cert) {
            $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split($cert, 64, "\n") . "-----END CERTIFICATE-----\n";
            $pems[] = $pem;
        }
        return $pems;
    }

    /**
     * 証明書チェーンの検証（複数ルートCA対応）
     * @param array<string> $certs
     * @param array<string> $appleRootPems
     * @return array{chainIndex: int, leafCert: string} 検証に使ったルートCAのindexとleafCert
     * @throws WpBillingException
     */
    private function validateCertificateChain(array $certs, array $appleRootPems): array
    {
        if (count($certs) === 0) {
            throw new WpBillingException('証明書チェーンが空です', ErrorCode::APPSTORE_JWS_SIGNATURE_INVALID);
        }
        foreach ($appleRootPems as $i => $appleRootPem) {
            // certs + appleRootPem をまとめてチェーンファイルに
            $fullChain = array_merge($certs, [$appleRootPem]);
            $chainPem = implode("\n", $fullChain);
            $chainFile = tempnam(sys_get_temp_dir(), 'jws_chain_');
            file_put_contents($chainFile, $chainPem);
            $leafCert = $certs[0];
            $res = openssl_x509_read($leafCert);
            if ($res === false) {
                // leaf証明書のパースに失敗
                @unlink($chainFile);
                continue;
            }
            $ok = openssl_x509_checkpurpose($res, X509_PURPOSE_ANY, [$chainFile]);
            @unlink($chainFile);
            if ($ok) {
                // ここに入らなかったら証明書チェーン検証に失敗している

                // どのルートCAで通ったか返す
                return ['chainIndex' => $i, 'leafCert' => $leafCert];
            }
        }
        // この行に到達することはない（必ず例外がスローされるか、上でreturnされる）
        throw new WpBillingException('証明書チェーン検証に失敗', ErrorCode::APPSTORE_JWS_SIGNATURE_INVALID);
    }

    /**
     * JWS署名の検証
     * 公開鍵を使用してJWSの署名を検証し、ペイロードを返す
     *
     * @param string $jws 検証対象のJWSトークン
     * @param mixed $publicKey 証明書から抽出した公開鍵
     * @return array<string, mixed> 検証済みペイロードデータ
     * @throws WpBillingException 署名検証に失敗した場合
     */
    private function verifySignature(string $jws, mixed $publicKey): array
    {
        $parts = explode('.', $jws);
        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $data = $header . '.' . $payload;
        $signatureBinary = base64_decode(strtr($signature, '-_', '+/'));


        // ES256署名の場合、r,sをDER形式に変換する必要がある
        if (strlen($signatureBinary) === 64) {
            $signatureBinary = $this->convertES256SignatureToDER($signatureBinary);
        }

        $verified = openssl_verify($data, $signatureBinary, $publicKey, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            throw new WpBillingException('JWS signature verification failed');
        }

        // ペイロードをデコードして返す
        return $this->decodePayloadOnly($jws);
    }

    /**
     * ES256署名をDER形式に変換
     * JWT/JWS標準のES256署名（r,s成分の連結）をASN.1 DER形式に変換
     *
     * @param string $signature 64バイトのES256署名（r:32バイト + s:32バイト）
     * @return string DER形式の署名データ
     * @throws WpBillingException 署名長が不正な場合
     */
    private function convertES256SignatureToDER(string $signature): string
    {
        // ES256署名はr,sが32バイトずつの64バイト
        if (strlen($signature) !== 64) {
            throw new WpBillingException('Invalid ES256 signature length');
        }

        $r = substr($signature, 0, 32);
        $s = substr($signature, 32, 32);

        // DER INTEGER形式にエンコード
        $rDer = $this->encodeDERInteger($r);
        $sDer = $this->encodeDERInteger($s);

        // DER SEQUENCE形式にエンコード
        $sequenceContent = $rDer . $sDer;
        return "\x30" . chr(strlen($sequenceContent)) . $sequenceContent;
    }

    /**
     * バイト配列をDER INTEGER形式にエンコード
     * ASN.1 DER規格に従って整数値をエンコード
     *
     * @param string $value エンコード対象のバイト配列
     * @return string DER INTEGER形式のデータ
     */
    private function encodeDERInteger(string $value): string
    {
        // 先頭バイトが0x80以上の場合は0x00を追加（2の補数表現のため）
        if (ord($value[0]) >= 0x80) {
            $value = "\x00" . $value;
        }
        return "\x02" . chr(strlen($value)) . $value;
    }

    /**
     * JWSトークンのハッシュ値を計算
     * rate limit対策のため、同一リクエスト判定に使用
     *
     * @param string $jws JWSトークン
     * @return string SHA256ハッシュ値
     */
    public function calculateJwsHash(string $jws): string
    {
        return hash('sha256', $jws);
    }

    /**
     * App Store Server API用JWTを自前生成
     * App Store Connect APIへの認証に使用するJWTトークンを生成
     *
     * @param string $environment AppStoreEnvironmentValidator::ENVIRONMENT_PRODUCTION
     *                            or AppStoreEnvironmentValidator::ENVIRONMENT_SANDBOX
     * @return string 生成されたJWTトークン
     * @throws WpBillingException 設定値不足または署名失敗の場合
     */
    public function createJwt(string $environment): string
    {
        // 必要な値は config から取得（例: issuer, key_id, private_key, bundle_id など）
        $issuer = config('wp_currency.store.app_store.storekit2.issuer');
        $keyId = config('wp_currency.store.app_store.storekit2.key_id');

        // 環境に応じてbundle_idを切り替え（StoreUtilityを使用）
        $bundleId = AppStoreEnvironmentValidator::isSandbox($environment)
            ? StoreUtility::getSandboxBundleId()
            : StoreUtility::getProductionBundleId();

        $privateKey = config('wp_currency.store.app_store.storekit2.private_key');
        if (!$issuer || !$keyId || !$privateKey) {
            throw new WpBillingException('JWT生成に必要な設定値が不足しています');
        }

        $now = time();
        $payload = [
            'iss' => $issuer,
            'iat' => $now,
            'exp' => $now + 1800, // 30分有効
            'aud' => 'appstoreconnect-v1',
            'bid' => $bundleId,
        ];
        $header = [
            'alg' => 'ES256',
            'kid' => $keyId,
            'typ' => 'JWT',
        ];

        // OpenSSLでES256署名
        $segments = [
            rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '='),
            rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '='),
        ];
        $signingInput = implode('.', $segments);
        $signature = '';
        $pkey = openssl_pkey_get_private($privateKey);
        if (!$pkey) {
            throw new WpBillingException('秘密鍵の取得に失敗しました');
        }
        if (!openssl_sign($signingInput, $signature, $pkey, 'sha256')) {
            throw new WpBillingException('JWT署名に失敗しました');
        }
        $segments[] = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return implode('.', $segments);
    }
}
