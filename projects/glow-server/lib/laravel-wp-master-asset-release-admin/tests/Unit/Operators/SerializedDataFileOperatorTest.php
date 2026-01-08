<?php

namespace MasterAssetReleaseAdmin\Unit\Operators;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use MessagePack\MessagePack;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\SerializedDataFileOperator;
use WonderPlanet\Entity\MasterEncryptionSetting;
use WonderPlanet\Tests\Support\RarityType;
use WonderPlanet\Tests\TestCase;

class SerializedDataFileOperatorTest extends TestCase
{
    private SerializedDataFileOperator $serializedDataFileOperator;
    private string $testDirPath = "";
    private string $testDataDirPath = "";

    protected $backupConfigKeys = [
        'app.env',
        'app.debug',
        'wp_encryption.master_data_enabled',
        'wp_encryption.master_data_password',
    ];

    private array $backupApp = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->serializedDataFileOperator = app(SerializedDataFileOperator::class);
        $disk = Storage::disk('local');
        if (!$disk->exists('testing/SerializedDataFileOperatorTest')) {
            // ディレクトリがなければ作成する
            $disk->makeDirectory('testing/SerializedDataFileOperatorTest');
        }
        $this->testDirPath = $disk->path('testing/SerializedDataFileOperatorTest');

        // テストデータを格納するディレクトリを取得
        // このファイルがあるところからの相対パスで取得する

        // MasterAssetReleaseAdmin以下のクラスパスを取得
        $class = explode("\\", self::class);
        $class = array_slice($class, 1);
        $class = implode("/", $class);

        // Dataディレクトリの下はnamespaceに合わせている
        $this->testDataDirPath = realpath(dirname(__FILE__) . "/../../Data/{$class}");

        // appを退避
        foreach (['app', 'config'] as $key) {
            $this->backupApp[$key] = app()[$key];
        }
    }

    public function tearDown(): void
    {
        $disk = Storage::disk('local');
        $disk->deleteDirectory('testing/SerializedDataFileOperatorTest');

        // appを元に戻す
        foreach ($this->backupApp as $key => $value) {
            app()[$key] = $value;
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function write_arrayデータをファイルに書き込む(): void
    {
        // Setup
        $path = $this->testDirPath . "/test.json.tmp";
        $pathOfMsgPack = $this->testDirPath . "/test.data";
        $data = [
            'testTable' => [
                [
                    'id' => 1,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Rare,
                    'start_at' => null,
                ],
                [
                    'id' => 2,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Common,
                    'start_at' => Carbon::now(),
                ],
            ],
        ];
        $expectedData = [
            'testTable' => [
                [
                    'id' => 1,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Rare->value,
                    'start_at' => null,
                ],
                [
                    'id' => 2,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Common->value,
                    'start_at' => Carbon::now()->toISOString(),
                ],
            ],
        ];


        // Exercise
        $this->serializedDataFileOperator->write($path, $pathOfMsgPack, $data);

        // Verify
        // jsonファイルをfile_getできるか
        $file = file_get_contents($path);
        $this->assertNotEquals(false, $file);
        $decode = json_decode($file, true);
        $this->assertEquals($expectedData, $decode);
        // messagePackファイルをfile_getできるか
        $file = file_get_contents($pathOfMsgPack);
        $this->assertNotEquals(false, $file);
        $unpack = MessagePack::unpack($file);
        $this->assertEquals($expectedData, $unpack);
    }

    /**
     * @test
     */
    public function write_stringデータをファイルに書き込む(): void
    {
        // Setup
        $path = $this->testDirPath . "/test.json.tmp";
        $pathOfMsgPack = $this->testDirPath . "/test.data";
        $data = "testString";

        // Exercise
        $this->serializedDataFileOperator->write($path, $pathOfMsgPack, $data);

        // Verify
        // jsonファイルをfile_getできるか
        $file = file_get_contents($path);
        $this->assertNotEquals(false, $file);
        $this->assertEquals($data, $file);
        // messagePackファイルをfile_getできるか
        $file = file_get_contents($pathOfMsgPack);
        $this->assertNotEquals(false, $file);
        $unpack = MessagePack::unpack($file);
        $this->assertEquals($data, $unpack);
    }

    public function writeNoJsonDataProvider(): array
    {
        return [
            'envがproduction' => [
                'env' => 'production',
                'debug' => true,
            ], 
            'debugがfalse' => [
                'env' => 'local',
                'debug' => false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider writeNoJsonDataProvider
     */
    public function write_本番環境以外ではjsonファイルを生成しない($env, $debug): void
    {
        // Setup
        config([
            'app.env' => $env,
            'app.debug' => $debug,
        ]);
        // app()->isProductionはapp()['env']を参照しているため、ここで設定する
        app()['env'] = $env;
        app()['config']->set('app.debug', $debug);

        $path = $this->testDirPath . "/test.json.tmp";
        $pathOfMsgPack = $this->testDirPath . "/test.data";
        $data = [
            'testTable' => [
                [
                    'id' => 1,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Rare,
                    'start_at' => null,
                ],
                [
                    'id' => 2,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Common,
                    'start_at' => Carbon::now(),
                ],
            ],
        ];
        $expectedData = [
            'testTable' => [
                [
                    'id' => 1,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Rare->value,
                    'start_at' => null,
                ],
                [
                    'id' => 2,
                    'release_key' => "2024111801",
                    'rare' => RarityType::Common->value,
                    'start_at' => Carbon::now()->toISOString(),
                ],
            ],
        ];


        // Exercise
        $this->serializedDataFileOperator->write($path, $pathOfMsgPack, $data);

        // Verify
        // jsonファイルは出力されていない
        $this->assertFalse(file_exists($path));

        // messagePackファイルをfile_getできるか
        $file = file_get_contents($pathOfMsgPack);
        $this->assertNotEquals(false, $file);
        $unpack = MessagePack::unpack($file);
        $this->assertEquals($expectedData, $unpack);
    }

    /**
     * @test
     */
    public function gzipAndEncryptMasterdataFile_圧縮暗号化をファイルに行う(): void
    {
        // Setup
        // 対象のファイルパス
        $testDataFile = $this->testDataDirPath . "/test.data.decrypted";
        $expectedDataFileMd5 = md5_file($this->testDataDirPath . "/test.data.encrypted");

        // 出力先
        $path = $this->testDirPath . "/test.data.encrypted";

        // エンコード設定
        $hash = '43f5cee44fe15fd7ee18ec4582a160ee';
        config([
            'wp_encryption.master_data_enabled' => true,
            'wp_encryption.master_data_password' => 'EdsX#s20m',
        ]);
        $setting = MasterEncryptionSetting::createUsingHash($hash);

        // Exercise
        $this->serializedDataFileOperator->gzipAndEncryptMasterdataFile($testDataFile, $path, $setting);

        // Verify
        $encryptedDataMd5 = md5_file($path);
        $this->assertEquals($expectedDataFileMd5, $encryptedDataMd5);
    }

    /**
     * @test
     */
    public function decryptAndDecompressMasterdataFile_復号解凍をファイルに行う(): void
    {
        // Setup
        // 対象のファイルパス
        $testDataFile = $this->testDataDirPath . "/test.data.encrypted";
        $expectedDataFileMd5 = md5_file($this->testDataDirPath . "/test.data.decrypted");

        // 出力先
        $path = $this->testDirPath . "/test.data";

        // エンコード設定
        $hash = '43f5cee44fe15fd7ee18ec4582a160ee';
        config([
            'wp_encryption.master_data_enabled' => true,
            'wp_encryption.master_data_password' => 'EdsX#s20m',
        ]);
        $setting = MasterEncryptionSetting::createUsingHash($hash);

        // Exercise
        $this->serializedDataFileOperator->decryptAndDecompressMasterdataFile($testDataFile, $path, $setting);

        // Verify
        $decryptedDataMd5 = md5_file($path);
        $this->assertEquals($expectedDataFileMd5, $decryptedDataMd5);
    }

    /**
     * @test
     */
    public function decryptAndDecompressMasterdataFile_復号解凍に失敗(): void
    {
        // Setup
        // 対象のファイルパス
        $testDataFile = $this->testDataDirPath . "/test.data.encrypted";

        // 出力先
        $path = $this->testDirPath . "/test.data";

        // エンコード設定
        $hash = '43f5cee44fe15fd7ee18ec4582a160ee';
        config([
            'wp_encryption.master_data_enabled' => true,
            'wp_encryption.master_data_password' => 'dummy',
        ]);
        $setting = MasterEncryptionSetting::createUsingHash($hash);

        // Exercise
        $this->expectException(\Exception::class);
        $this->serializedDataFileOperator->decryptAndDecompressMasterdataFile($testDataFile, $path, $setting);
    }
}
