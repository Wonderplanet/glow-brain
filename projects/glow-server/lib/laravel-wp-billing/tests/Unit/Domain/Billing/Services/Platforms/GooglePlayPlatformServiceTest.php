<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Constants\ErrorCode as ConstantsErrorCode;
use WonderPlanet\Domain\Billing\Exceptions\WpBillingException;
use WonderPlanet\Domain\Billing\Services\Platforms\GooglePlayPlatformService;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * GooglePlayストアプラットフォームと連携するServiceのテスト
 *
 * ユニットテスト時にプラットフォームへの問い合わせを行うため、通常は実行しないようにしているテストがある。
 * 実行する際は--group wp_billing_googleplayを指定すること。
 *
 * またユニットテストで実行する場合は、秘密鍵JSONファイルなど.envに設定するストア関連の設定を環境変数に設定する。
 * .env.local_testがGitHubにコミットされているため、そこにIDを記載したくないので
 * テストするときは環境変数で上書きを行う。
 */
class GooglePlayPlatformServiceTest extends TestCase
{
    private GooglePlayPlatformService $googlePlayPlatformService;

    private $changedEnv = [];
    private $changeConfig = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->googlePlayPlatformService = $this->app->make(GooglePlayPlatformService::class);

        // テスト内で変更する環境変数の値を保存しておく
        foreach ([Config::get('wp_currency.store.googleplay_store.pubkey_env_key')] as $key) {
            $this->changedEnv[$key] = getenv($key);
        };
        // テスト内で変更するconfigの値を保存しておく
        foreach (['wp_currency.store.googleplay_store.pubkey'] as $key) {
            $this->changeConfig[$key] = Config::get($key);
        };
    }

    protected function tearDown(): void
    {
        // 変更した環境変数を戻す
        foreach ($this->changedEnv as $key => $value) {
            putenv($key . '=' . $value);
        }
        // 変更したconfigを戻す
        foreach ($this->changeConfig as $key => $value) {
            Config::set($key, $value);
        }

        parent::tearDown();
    }

    #[Test]
    #[Group('wp_billing_googleplay')]
    public function verifyReceipt_レシートの確認()
    {
        // Setup
        //  テスト用のレシートを読み込む
        $receipt = $this->getSandboxReceipt();

        // Exercise
        $storeReceipt = $this->googlePlayPlatformService->verifyReceipt(
            CurrencyConstants::PLATFORM_APPSTORE,
            'android_edmo_pack_160_1_framework',
            $receipt
        );

        // Verify
        //  receiptの内容によって結果が変わってくるため、詳細な称号は行わない
        print_r([
            'unique id' => $storeReceipt->getUnitqueId(),
            'sandbox' => $storeReceipt->isSandboxReceipt(),
            'purchase token' => $storeReceipt->getPurchaseToken(),
        ]);
        $this->assertTrue(true);
    }

    #[Test]
    #[Group('wp_billing_googleplay')]
    public function verifyReceiptToGooglePlayClient_ストアレシートの検証()
    {
        // Setup
        $receipt = $this->getSandboxReceipt();
        $receiptJson = json_decode($receipt, true);
        $payload = json_decode($receiptJson['Payload'], true);

        // Exercise
        $response = $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptToGooglePlayClient',
            [$payload['json']]
        );

        // Verify
        //  receiptの内容によって結果が変わってくるため、詳細な照合は行わない
        //  responseの出力のみ行う
        print_r($response);
        $this->assertTrue(true);
    }

    #[Test]
    #[Group('wp_billing_googleplay')]
    public function verifyReceiptSignature_サンプル採取したレシート署名の検証()
    {
        // Setup
        // 採取したサンドボックス向けレシートを読み込む
        $receipt = $this->getSandboxReceipt();
        $receiptJson = json_decode($receipt, true);
        $payload = json_decode($receiptJson['Payload'], true);

        // Exercise
        $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptSignature',
            [
                $payload['json'],
                $payload['signature']
            ]
        );

        // Verify
        //  成功すると何も返ってこない
        $this->assertTrue(true);
    }

    #[Test]
    public function verifyReceiptSignature_署名したデータの検証()
    {
        // Setup
        $json = '{"orderId":"test"}';
        $privateKey = $this->getDummyPrivateKey();
        $publicKey = $this->getDummyPublicKey();
        openssl_sign($json, $signature, $privateKey, OPENSSL_ALGO_SHA1);

        // getPlayStorePubKeyで取得するよう、環境変数に設定
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        putenv($envkey . '=' . $publicKey);

        // Exercise
        $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptSignature',
            [
                $json,
                base64_encode($signature)
            ]
        );

        // Verify
        //  成功すると何も返ってこない
        $this->assertTrue(true);
    }

    #[Test]
    public function verifyReceiptSignature_公開鍵が正しく読み込めなかった()
    {
        // Setup
        $json = '{"orderId":"test"}';
        $privateKey = $this->getDummyPrivateKey();
        openssl_sign($json, $signature, $privateKey, OPENSSL_ALGO_SHA1);

        // getPlayStorePubKeyで取得するよう、環境変数に設定
        // 公開鍵として正しくない値を設定
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        putenv($envkey . '=' . 'dummy');

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ConstantsErrorCode::GOOGLEPLAY_PUBLIC_KEY_LOAD_FAILED);
        $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptSignature',
            [
                $json,
                base64_encode($signature)
            ]
        );
    }

    #[Test]
    public function verifyReceiptSignature_公開鍵が一致しなかった()
    {
        // Setup
        $json = '{"orderId":"test"}';
        $privateKey = $this->getDummyPrivateKey();
        openssl_sign($json, $signature, $privateKey, OPENSSL_ALGO_SHA1);

        // getPlayStorePubKeyで取得するよう、環境変数に設定
        // 署名した秘密鍵とは別の公開鍵を設定
        $otherPublickey = <<< KEY
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv7iL3GRGDrkYC0nkGVES
            CNt9czbAvodKbctPTRjPjhKeNSTazcBjB1UhYW9XkjXLT/YXWtzVsgiK/0HYSyeR
            n+iylWQ2VN3an4sm4iwSyzYe48E+O4UExswpEMfbFgUh068YxEKaTlcr4NYtzcPN
            4ZuNfHL97jyA/OC4JA8ePWE88s8W2gBiEbc9OCDwusJJpGffDxYmL6GBLKTuyVlt
            EZOOYTc+pFgV+TR222pRgAZPViOf4R6P3mQraRbdi/BuPC8ITiCK4cYDgFE9GPjP
            HUhOaUdVDT2ZcsVPO5BVxXFjgyjZAQtTum+a5ZHSHqZ8ylkx7rx1vAveGPhUpXmc
            FwIDAQAB
            -----END PUBLIC KEY-----
            KEY;
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        putenv($envkey . '=' . $otherPublickey);

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ConstantsErrorCode::INVALID_RECEIPT);

        $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptSignature',
            [
                $json,
                base64_encode($signature)
            ]
        );
    }

    #[Test]
    public function verifyReceiptSignature_signatureが正しくなかった()
    {
        // Setup
        $publicKey = $this->getDummyPublicKey();
        
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        putenv($envkey . '=' . $publicKey);

        // Exercise
        $this->expectException(WpBillingException::class);
        $this->expectExceptionCode(ConstantsErrorCode::INVALID_RECEIPT);

        $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptSignature',
            [
                '{"orderId":"test"}',
                '@@@'
            ]
        );
    }

    #[Test]
    #[Group('wp_billing_googleplay')]
    public function purchaseAcknowledge_購入を承認する()
    {
        // Setup
        $receipt = $this->getSandboxReceipt();
        $receiptJson = json_decode($receipt, true);
        $payload = json_decode($receiptJson['Payload'], true);

        // Exercise
        $this->callMethod(
            $this->googlePlayPlatformService,
            'purchaseAcknowledge',
            [$payload['json']]
        );

        // Verify
        //  成功すると何も返ってこない

        // 承認されたレシートを検証してみて、その内容を表示
        $response = $this->callMethod(
            $this->googlePlayPlatformService,
            'verifyReceiptToGooglePlayClient',
            [$payload['json']]
        );
        print_r($response);

        $this->assertTrue(true);
    }

    #[Test]
    public function getPlayStorePubKey_ファイルからの読み込み()
    {
        // Setup
        // 対象のファイルパス
        $fixtureDir = $this->getFixtureDir();
        $pubkey = $fixtureDir . '/dummy_pubkey.pub';
        $expected = $this->getDummyPublicKey();

        // configに設定されているファイルパスを書き換え
        Config::set('wp_currency.store.googleplay_store.pubkey', $pubkey);

        // Exercise
        $result = $this->callMethod(
            $this->googlePlayPlatformService,
            'getPlayStorePubKey',
            [$pubkey]
        );

        // Verify
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function getPlayStorePubKey_ファイルからフィルタを通して取得していること()
    {
        // Setup
        // 対象のファイルパス
        $fixtureDir = $this->getFixtureDir();
        $pubkey = $fixtureDir . '/dummy_pubkey_noreturn.pub';
        $expected = $this->getDummyPublicKey();

        // configに設定されているファイルパスを書き換え
        Config::set('wp_currency.store.googleplay_store.pubkey', $pubkey);

        // Exercise
        $result = $this->callMethod(
            $this->googlePlayPlatformService,
            'getPlayStorePubKey',
            [$pubkey]
        );

        // Verify
        $this->assertIsString($result);
        // 想定通りの文字列になっていること
        $expected = <<< KEY
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB
            -----END PUBLIC KEY-----
            KEY;
        $this->assertEquals($expected, $result);
        // openssl_pkey_get_publicで問題ないこと
        // 失敗したらfalseになるため、オブジェクトであることを確認できればよい
        $this->assertIsObject(openssl_pkey_get_public($result));
    }

    #[Test]
    public function getPlayStorePubKey_環境変数からの読み込み()
    {
        // Setup
        $pubkey = $this->getDummyPublicKey();
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        putenv($envkey . '=' . $pubkey);

        // Exercise
        $result = $this->callMethod(
            $this->googlePlayPlatformService,
            'getPlayStorePubKey',
            [null]
        );

        // Verify
        $this->assertEquals($pubkey, $result);
    }

    #[Test]
    public function getPlayStorePubKey_環境変数からフィルタを通して取得していること()
    {
        // Setup
        $pubkey = '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB\n-----END PUBLIC KEY-----';
        $envkey = Config::get('wp_currency.store.googleplay_store.pubkey_env_key');
        putenv($envkey . '=' . $pubkey);

        // Exercise
        $result = $this->callMethod(
            $this->googlePlayPlatformService,
            'getPlayStorePubKey',
            [null]
        );

        // Verify
        $this->assertIsString($result);
        // 想定通りの文字列になっていること
        $expected = <<< KEY
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB
            -----END PUBLIC KEY-----
            KEY;
        $this->assertEquals($expected, $result);
        // openssl_pkey_get_publicで問題ないこと
        // 失敗したらfalseになるため、オブジェクトであることを確認できればよい
        $this->assertIsObject(openssl_pkey_get_public($result));
    }

    public static function formatPlayStorePubKeyData(): array
    {
        return [
            '正常' => [<<< KEY
                -----BEGIN PUBLIC KEY-----
                MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB
                -----END PUBLIC KEY-----
                KEY],
            '改行コード' => ['-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB\n-----END PUBLIC KEY-----'],
            '改行なし' => ['-----BEGIN PUBLIC KEY-----MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB-----END PUBLIC KEY-----'],
            '空白' => ['-----BEGIN PUBLIC KEY----- MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB -----END PUBLIC KEY-----'],
            'タブ' => ["-----BEGIN PUBLIC KEY-----\tMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB\t-----END PUBLIC KEY-----"],
            'BEGINなし' => ['MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB\n-----END PUBLIC KEY-----'],
            'ENDなし' => ['-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB'],
            'BEGINとENDなし' => ['MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB'],
        ];
    }

    /**
     * 公開鍵の取得
     * フォーマットを揃えて取得する
     */
    #[Test]
    #[DataProvider('formatPlayStorePubKeyData')]
    public function formatPlayStorePubKey_環境変数からの鍵の取得($key)
    {
        // Exercise
        $result = $this->callMethod(
            $this->googlePlayPlatformService,
            'formatPlayStorePubKey',
            [$key]
        );

        // Verify
        $this->assertIsString($result);
        // 想定通りの文字列になっていること
        $expected = <<< KEY
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp+xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJi+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afWcchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT662QIDAQAB
            -----END PUBLIC KEY-----
            KEY;
        $this->assertEquals($expected, $result);
        // openssl_pkey_get_publicで問題ないこと
        // 失敗したらfalseになるため、オブジェクトであることを確認できればよい
        $this->assertIsObject(openssl_pkey_get_public($result));
    }

    #[Test]
    public function formatPlayStorePubKey_PUBLIC_KEYの形式が指定されていても消えないこと()
    {
        // Setup
        $key = '-----BEGIN SSH2 PUBLIC KEY-----\nsample\n-----END SSH2 PUBLIC KEY-----';
        $expected = <<< KEY
            -----BEGIN SSH2 PUBLIC KEY-----
            sample
            -----END SSH2 PUBLIC KEY-----
            KEY;

        // Exercise
        $result = $this->callMethod(
            $this->googlePlayPlatformService,
            'formatPlayStorePubKey',
            [$key]
        );

        // Verify
        $this->assertEquals($expected, $result);
    }

    #[Test]
    #[Group('wp_billing_googleplay')]
    public function getProductIds_ProductIDが取得できる()
    {
        // Setup
        //  テスト用のレシートを読み込む
        $receipt = $this->getSandboxReceipt();

        // Exercise
        $storeReceipt = $this->googlePlayPlatformService->verifyReceipt(
            CurrencyConstants::PLATFORM_APPSTORE,
            'android_edmo_pack_160_1_framework',
            $receipt
        );

        // Verify
        $this->assertEquals(true, !empty($storeReceipt->getProductIds()[0]));
    }

    #[Test]
    #[Group('wp_billing_googleplay')]
    public function getPurchaseDate_購入日時が取得できる()
    {
        // Setup
        $receipt = $this->getSandboxReceipt();
        $receiptJson = json_decode($receipt, true);
        $payload = json_decode($receiptJson['Payload'], true);
        // payloadのjson内からpurchaseTimeを取得
        // ref: https://docs.unity3d.com/ja/2023.2/Manual/UnityIAPPurchaseReceipts.html
        $json = json_decode($payload['json'], true);
        $purchaseTime = $json['purchaseTime'] ?? null;

        // Exercise
        $storeReceipt = $this->googlePlayPlatformService->verifyReceipt(
            CurrencyConstants::PLATFORM_APPSTORE,
            'android_edmo_pack_160_1_framework',
            $receipt
        );

        // Verify
        // purchaseTimeMillisとpurchaseTimeは同じ値だったため双方の日時を確認する
        // ref: https://app.clickup.com/t/86ert7y64?comment=90180109276138
        $this->assertEquals(
            \Carbon\Carbon::createFromTimestamp(substr("{$purchaseTime}", 0, -3))->toDateTimeString(),
            $storeReceipt->getPurchaseDate()->toDateTimeString(),
        );
    }


    /**
     * テストに使用するサンドボックスレシートを読み込む
     *
     * @return string
     */
    private function getSandboxReceipt(): string
    {
        $receiptPath = Config::get('wp_currency.store_test.googleplay_store.googleplay_sandbox_receipt');

        $receipt = file_get_contents($receiptPath);

        return $receipt;
    }

    /**
     * テストで使用する秘密鍵を取得する
     *
     * @return string
     */
    private function getDummyPrivateKey(): string
    {
        // openssl_pkey_new() で作成した公開鍵と秘密鍵
        // 余計な改行が入らないように、インデントも含めないヒアドキュメントで定義
        // $res = openssl_pkey_new();
        // openssl_pkey_export($res, $privateKey);
        $privateKey = <<< KEY
            -----BEGIN PRIVATE KEY-----
            MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCoYd1dUBEhMfY3
            zCUcw6n7Em8dXfoIYWDzeTqYLBirfK2GeZFEkk7HcE+rUG6w+7Vm8RD2H0EBUGod
            zpnFinZNiwAk+ydp8nSOS/3UbjRQoUcTCQUlXZ2WoY4Iw564dARomTksE3FJ/E7C
            LSe5JImL4sxduax3JEyViYWq7u9+qUSJac4X1m2SigMVzYTufIZ9XK2sV3UGaKqV
            LkrE7DsxbDiyhKpzx7mwuw8EkWH1YRJtJGAIWMljskVJhV4p4aaVdC3k77L6nzlV
            g7Tdp9ZxyE60C1kRW/Z6dRrxz6bTZ3Bwmy3Df9Y5BTLZOiYvHEzO+srLEqqvs3mD
            6ra5PrrZAgMBAAECggEADNpPheBnNlP0eeTczlnHH1GUZrb2L26TcnJF/Ticd3aQ
            XkvoQUYzujiB9E8y69KC0cVD6K2RDjMfrn/HMBN2HUwnais7onQt3nDBgtYYqzDs
            VnEhUe1X6pgRWezosCa28W8EtK8VPjMCpgLBXfoCf8mDlQHnvr5oFCAHsnkDkfTk
            9Zl7sOBO3okGjM4PL0RqswUFqNcKG4KVXszEUvyoaME1g4j73WAcC0EAkmgJOPv5
            alASMNDaGCoXsHFsamG1h4aHaxWhY/7DEzJnsTDFUEmIDq7LKtvU6fC2wMHdCott
            Lo02A7oV5IM+iFe5Yu/r0fok6jx99gwf8XcyvGX/4wKBgQDEJ+SFMJtNH4nygaOc
            X2pCOg4plYPqhG1TFs/XHlVXGpNTZnebB6teaN5z1bqGiiUUXYbJY6KnwZ3ELKeN
            P6tPmLT1zUwbFjAXUY6p2ScMqZDN4610oGcxhzpp6welZJQ7OccvpIiHFKmUmhvu
            Um3rh+yK9jpNOzscaosuN+9ypwKBgQDbwM9yfVnlRia5AJ9NlgagTah/PBI08QvM
            61Z83+5nj4/HgEZNz6V0+i02/PbcL3pnnn7IAcUav9QVT+Gv3b6y1Vb0QT3IedsH
            0PaB1vWyKYHeGAtN9IAAP1GvPKLqGiIOWSvuuHvydcuCOV+U8KYnQEBUOwUHvF2m
            ruxUTWGWfwKBgE6iL1m1QoyIOCSfE5d8KHykCUliRp+ctra4TllOL6fbX3Pvf8MG
            MAyIvaRx6XRFrNedJotVBb96PmSGAiT9gQ9HiEOBKSEyo9S1PRuZka3hy8q9mqtG
            IhgYvbH4JfiHeWTEpLTUoGaGQfTwUoIXSTlCI/ERBA4x0GBz1ZeRlMvBAoGBALJa
            LIPZZMqGQwtHjANmTf5wyN5rHMPHFzK7ljhHbrCyfZkHbQfeDYWBPo0whhJynj/X
            DUK63QQ2yKR7bspTiGCQccBP6xr4e2I+oLMEieiNc4+TqCke1Xxd56f36KljiFxo
            1xUqub4xCHiqo/63ycJ1jUBPnmeG4+NSeRB6tUd1AoGBAJQkUik0tsa49WfqvHSZ
            1mY8M0dn9w3w6I1vQr/wPbrxynzWgqAr4BcQ4095XfyP7HeBEusXRzUQIRIuw0Ks
            DYstsU5BTwpCr5LJoQJLG10K3fLer5fHnntEse0JUj0iMBgwMadmst2ZBRE//rFr
            J2v/25swBFwUE+HjDnehJIwn
            -----END PRIVATE KEY-----
            KEY;

        return $privateKey;
    }

    /**
     * テストで使用する公開鍵を取得する
     *
     * @return string
     */
    private function getDummyPublicKey(): string
    {
        // openssl_pkey_new() で作成した公開鍵と秘密鍵
        // $res = openssl_pkey_new();
        // $publicKey = openssl_pkey_get_details($res)['key'];
        $publicKey = <<< KEY
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqGHdXVARITH2N8wlHMOp
            +xJvHV36CGFg83k6mCwYq3ythnmRRJJOx3BPq1BusPu1ZvEQ9h9BAVBqHc6ZxYp2
            TYsAJPsnafJ0jkv91G40UKFHEwkFJV2dlqGOCMOeuHQEaJk5LBNxSfxOwi0nuSSJ
            i+LMXbmsdyRMlYmFqu7vfqlEiWnOF9ZtkooDFc2E7nyGfVytrFd1BmiqlS5KxOw7
            MWw4soSqc8e5sLsPBJFh9WESbSRgCFjJY7JFSYVeKeGmlXQt5O+y+p85VYO03afW
            cchOtAtZEVv2enUa8c+m02dwcJstw3/WOQUy2TomLxxMzvrKyxKqr7N5g+q2uT66
            2QIDAQAB
            -----END PUBLIC KEY-----
            KEY;

        return $publicKey;
    }

    /**
     * テスト用のファイルパスを取得する
     * クラス名からディレクトリを生成している
     *
     * @return string
     */
    private function getFixtureDir(): string
    {
        // ファイルパスからテストのルートディレクトリを取得
        $testdir = realpath(__DIR__ . '/../../../../../');

        // namespaceとclassnameからディレクトリを作成
        $reflectionClass = new \ReflectionClass(static::class);
        $name = $reflectionClass->getName();
        // WonderPlanet\Testsを除いた文字列をパスに変換
        $name = str_replace('WonderPlanet\Tests', '', $name);
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        $fixtureDir = realpath(implode(DIRECTORY_SEPARATOR, [$testdir, 'fixtures', $path]));

        return $fixtureDir;
    }
}
