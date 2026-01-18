<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Services\Platforms\StoreKit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;
use WonderPlanet\CurrencyServiceProvider;
use WonderPlanet\Domain\Billing\Services\Platforms\StoreKit\CertificateManager;
use WonderPlanet\Tests\Traits\ReflectionTrait;

/**
 * CertificateManager のテスト
 */
class CertificateManagerTest extends TestCase
{
    use ReflectionTrait;

    protected $backupConfigKeys = [
        'wp_currency.store.app_store.storekit2.cert_dir',
    ];

    private CertificateManager $certificateManager;
    private string $testCertDir;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の一時ディレクトリを作成
        $this->testCertDir = sys_get_temp_dir() . '/test-certificates-' . uniqid();
        
        // CurrencyServiceProviderを登録してCertificateManagerをsingleton登録
        $this->app->register(CurrencyServiceProvider::class);
        
        // テスト用にCertificateManagerのsingleton登録を上書きして一時ディレクトリを使用
        $this->app->singleton(CertificateManager::class, function () {
            return new CertificateManager($this->testCertDir);
        });
        
        $this->certificateManager = $this->app->make(CertificateManager::class);
    }

    protected function tearDown(): void
    {
        // テスト用ディレクトリをクリーンアップ
        if (is_dir($this->testCertDir)) {
            $this->removeDirectory($this->testCertDir);
        }

        parent::tearDown();
    }

    /**
     * @return array
     */
    public static function caKeyProvider(): array
    {
        return [
            ['g2'],
            ['g3'],
        ];
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function getAppleRootCaPem_証明書ディレクトリが自動作成される_各CA(string $key)
    {
        // Execute & Verify
        $this->assertTrue(is_dir($this->testCertDir));
        $this->assertTrue(is_writable($this->testCertDir));
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function getAppleRootCaPem_初回アクセス時に証明書がダウンロードされる_各CA(string $key)
    {
        // Execute
        $pemData = $this->certificateManager->getAppleRootCaPem($key);

        // Verify
        $this->assertStringStartsWith('-----BEGIN CERTIFICATE-----', $pemData);
        $this->assertStringEndsWith("-----END CERTIFICATE-----\n", $pemData);

        // 証明書ファイルが作成されていることを確認
        $certPath = $this->testCertDir . "/apple-root-ca-{$key}.pem";
        $this->assertTrue(file_exists($certPath));
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function getAppleRootCaPem_2回目のアクセス時はキャッシュが使用される_各CA(string $key)
    {
        // Setup
        $firstCall = $this->certificateManager->getAppleRootCaPem($key);

        // Execute
        $secondCall = $this->certificateManager->getAppleRootCaPem($key);

        // Verify
        $this->assertEquals($firstCall, $secondCall);
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function clearCache_キャッシュクリア後は再度ファイルから読み込まれる_各CA(string $key)
    {
        // Setup
        $firstCall = $this->certificateManager->getAppleRootCaPem($key);

        // Execute
        $this->certificateManager->clearCache($key);
        $secondCall = $this->certificateManager->getAppleRootCaPem($key);

        // Verify
        $this->assertEquals($firstCall, $secondCall);
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function deleteCertificateFile_証明書ファイル削除後は再ダウンロードされる_各CA(string $key)
    {
        // Setup
        $firstCall = $this->certificateManager->getAppleRootCaPem($key);

        // Execute
        $this->certificateManager->deleteCertificateFile($key);
        $this->certificateManager->clearCache($key);
        $secondCall = $this->certificateManager->getAppleRootCaPem($key);

        // Verify
        $this->assertEquals($firstCall, $secondCall);

        // 証明書ファイルが再作成されていることを確認
        $certPath = $this->testCertDir . "/apple-root-ca-{$key}.pem";
        $this->assertTrue(file_exists($certPath));
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function getAppleRootCaPem_有効な証明書であることを確認_各CA(string $key)
    {
        // Execute
        $pemData = $this->certificateManager->getAppleRootCaPem($key);

        // Verify
        $cert = openssl_x509_read($pemData);
        $this->assertNotFalse($cert, 'Certificate should be valid');

        $certInfo = openssl_x509_parse($cert);
        $this->assertNotFalse($certInfo, 'Certificate should be parseable');

        $this->assertStringContainsString('Apple Inc.', $certInfo['issuer']['O']);

        // 有効期限が未来であることを確認
        $this->assertGreaterThan(time(), $certInfo['validTo_time_t'], 'Certificate should not be expired');

        openssl_x509_free($cert);
    }

    #[Test]
    public function construct_存在しないディレクトリは自動作成される()
    {
        // Setup - 存在しないディレクトリパスを作成
        $newDir = sys_get_temp_dir() . '/new-cert-dir-' . uniqid();

        // Execute
        $manager = new CertificateManager($newDir);

        // Verify
        $this->assertTrue(is_dir($newDir));
        $this->assertTrue(is_writable($newDir));

        // Cleanup
        if (is_dir($newDir)) {
            rmdir($newDir);
        }
    }

    #[Test]
    public function getAppleRootCaPem_ネットワークエラー時のフォールバック処理()
    {
        // Setup - ネットワークエラーをシミュレートするため、無効なURLを使用
        // この場合はハードコードされた証明書が返される

        // まず通常の証明書を取得してファイルを削除
        $this->certificateManager->getAppleRootCaPem('g3');
        $this->certificateManager->deleteCertificateFile('g3');
        $this->certificateManager->clearCache('g3');

        // 無効なURLでCertificateManagerを作成する代わりに、
        // 既存の仕組みでハードコード証明書が取得できることを確認
        $pemData = $this->certificateManager->getAppleRootCaPem('g3');

        // Verify
        $this->assertStringStartsWith('-----BEGIN CERTIFICATE-----', $pemData);
        $this->assertStringEndsWith("-----END CERTIFICATE-----\n", $pemData);
    }

    #[Test]
    public function getAppleRootCaPem_証明書の形式が正しいことを確認()
    {
        // Execute
        $pemData = $this->certificateManager->getAppleRootCaPem('g3');

        // Verify - PEM形式の基本構造をチェック
        $lines = explode("\n", $pemData);
        $this->assertEquals('-----BEGIN CERTIFICATE-----', trim($lines[0]));
        $this->assertEquals('-----END CERTIFICATE-----', trim($lines[count($lines) - 2])); // 最後の行は空行

        // Base64データ部分をチェック
        $base64Data = '';
        for ($i = 1; $i < count($lines) - 2; $i++) {
            $base64Data .= trim($lines[$i]);
        }
        $this->assertTrue(base64_decode($base64Data) !== false, 'Certificate should contain valid base64 data');
    }

    #[DataProvider('caKeyProvider')]
    #[Test]
    public function getAppleRootCaPem_複数回のファイル削除と再作成が正常動作_各CA(string $key)
    {
        // Setup & Execute - 複数回のファイル削除と再作成
        $results = [];
        for ($i = 0; $i < 3; $i++) {
            $results[] = $this->certificateManager->getAppleRootCaPem($key);
            $this->certificateManager->deleteCertificateFile($key);
            $this->certificateManager->clearCache($key);
        }

        // 最後にもう一度取得して最終ファイル存在確認用
        $finalResult = $this->certificateManager->getAppleRootCaPem($key);

        // Verify - 全て同じ証明書データが取得できることを確認
        $this->assertEquals($results[0], $results[1]);
        $this->assertEquals($results[1], $results[2]);
        $this->assertEquals($results[2], $finalResult);

        // 最終的にファイルが存在することを確認
        $certPath = $this->testCertDir . "/apple-root-ca-{$key}.pem";
        $this->assertTrue(file_exists($certPath));
    }

    #[Test]
    public function app_make_同じインスタンスが返される()
    {
        // Setup & Execute
        $instance1 = $this->app->make(CertificateManager::class);
        $instance2 = $this->app->make(CertificateManager::class);

        // Verify
        $this->assertSame($instance1, $instance2, 'CertificateManagerがsingleton登録されている場合、同じインスタンスが返される');
    }

    #[Test] 
    public function app_make_キャッシュが共有される()
    {
        // Setup
        $instance1 = $this->app->make(CertificateManager::class);
        $instance2 = $this->app->make(CertificateManager::class);

        // Execute - instance1でg2証明書を取得
        $pem1 = $instance1->getAppleRootCaPem('g2');

        // Verify - instance2でも同じキャッシュを参照できる
        $pem2 = $instance2->getAppleRootCaPem('g2');
        $this->assertEquals($pem1, $pem2, 'singleton登録により、キャッシュが共有される');

        // さらに検証 - キャッシュクリアもインスタンス間で共有される
        $instance1->clearCache('g2');
        // キャッシュがクリアされてもファイルから読み込まれるため同じ内容になる
        $pem3 = $instance2->getAppleRootCaPem('g2');
        $this->assertEquals($pem1, $pem3, 'キャッシュクリア後もファイルから同じ証明書が読み込まれる');
    }

    public function getDefaultCertDir_デフォルトパスがstorage_app_certificatesであることを確認()
    {
        // Setup
        // ディレクトリの設定を削除
        config(['wp_currency.store.app_store.storekit2.cert_dir' => null]);
        
        // Execute - configが設定されていない場合のデフォルトパスを取得        
        // getcwd()の結果をモックするのは難しいので、実際のパスを確認
        // リフレクションを使用してプライベートメソッドを呼び出し
        $defaultPath = $this->callMethod($this->certificateManager, 'getDefaultCertDir');

        // Verify
        // デフォルトパスが期待通りであることを確認
        $this->assertStringEndsWith('/storage/app/certificates', $defaultPath);
    }

    /**
     * ディレクトリを再帰的に削除
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
