<?php

namespace MasterAssetReleaseAdmin\Unit\Services;

use Illuminate\Support\Facades\Storage;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Entities\DatabaseCsvEntity;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\SerializeDataGenerateService;
use WonderPlanet\Tests\TestCase;

class SerializeDataGenerateServiceTest extends TestCase
{
    use ReflectionTrait;

    private SerializeDataGenerateService $service;

    private string $testDirPath = "";
    private string $testDataDirPath = "";

    protected $backupConfigKeys = [
        'wp_encryption.master_data_enabled',
        'wp_encryption.master_data_password',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(SerializeDataGenerateService::class);

        // MasterAssetReleaseAdmin以下のクラスパスからディレクトリパスを取得
        $classDirPath = explode("\\", self::class);
        $classDirPath = array_slice($classDirPath, 1);
        $classDirPath = implode("/", $classDirPath);

        $disk = Storage::disk('local');
        $testDirPath = 'testing' . DIRECTORY_SEPARATOR . 'SerializeDataGenerateServiceTest';
        if (!$disk->exists($testDirPath)) {
            // ディレクトリがなければ作成する
            $disk->makeDirectory($testDirPath);
        }
        $this->testDirPath = $disk->path($testDirPath);

        // テストデータを格納するディレクトリを取得
        // このファイルがあるところからの相対パスで取得する

        // Dataディレクトリの下はnamespaceに合わせている
        $this->testDataDirPath = realpath(dirname(__FILE__) . "/../../Data/{$classDirPath}");

        // パスワードは適当なものに設定
        config([
            'wp_encryption.master_data_enabled' => true,
            'wp_encryption.master_data_password' => 'EdsX#s20m',
        ]);
    }

    public function tearDown(): void
    {
        // テストデータを削除
        // $this->testDirPathから$distのパスを除いて削除する
        $disk = Storage::disk('local');
        // $disk->deleteDirectory(str_replace($disk->path(''), '', $this->testDirPath));

        parent::tearDown();
    }

    public function outputMessagePackAndJsonDataProvider(): array
    {
        return [
            '暗号化' => [
                // 暗号化設定
                true,
                // 返却されるハッシュの照合ファイル名
                'masterdata.data.decrypted',
                // アウトプットファイルの照合ファイル名
                'masterdata.data.encrypted',
            ],
            '暗号化なし' => [
                // 非暗号化設定
                false,
                // 返却されるハッシュの照合ファイル名
                'masterdata.data.decrypted',
                // アウトプットファイルの照合ファイル名
                'masterdata.data.decrypted',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider outputMessagePackAndJsonDataProvider
     */
    public function outputMessagePackAndJson_MessagePackとJSONファイルを出力(
        bool $encrypt,
        string $expectedHashFileName,
        string $expectedEncryptedFileHashFileName
    ): void {
        // Setup
        config([
            'wp_encryption.master_data_enabled' => $encrypt,
        ]);

        $targetMstEntities = [
            new DatabaseCsvEntity([
                // ヘッダ
                [
                    'id',
                    'release_key',
                    'rarity',
                    'item_key',
                    'asset_key',
                    'category',
                ],
                // データ
                [
                    1,
                    '20230101',
                    'Common',
                    1,
                    'dragon_fruit',
                    'Ticket',
                ],
            ], 'MstItem'),
        ];
        $serializedFileDirPath = $this->testDirPath;
        $targetNameSpace = config('wp_master_asset_release_admin.clientMasterdataModelNameSpace.masterData');
        $target = MasterData::MASTERDATA;
        // release_keyを別途設定
        foreach ($targetMstEntities as $targetMstEntity) {
            $targetMstEntity->setReleaseKey('20230101');
        }
        // 期待されるハッシュ値
        // 返ってくるハッシュ値は暗号化前のファイルハッシュとなる
        $expectedHash = md5_file($this->testDataDirPath . DIRECTORY_SEPARATOR . $expectedHashFileName);
        $expectedOutputFileHash = md5_file($this->testDataDirPath . DIRECTORY_SEPARATOR . $expectedEncryptedFileHashFileName);

        // Exercise
        $hash = $this->callMethod(
            $this->service,
            'outputMessagePackAndJson',
            $targetMstEntities,
            $serializedFileDirPath,
            $targetNameSpace,
            $target
        );

        // Verify
        // 取得するハッシュの確認 (圧縮前のファイルハッシュになる)
        $this->assertEquals($expectedHash, $hash);
        // 生成されたデータのハッシュチェック
        $outputfile = implode(DIRECTORY_SEPARATOR, [$serializedFileDirPath, $target, 'masterdata_' . $hash . '.data']);
        $outputFileHash = md5_file($outputfile);
        $this->assertEquals($expectedOutputFileHash, $outputFileHash);
    }

    public function outputMessagePackAndJsonByI18nDataProvider(): array
    {
        return [
            '暗号化' => [
                // 暗号化設定
                true,
                // 返却されるハッシュの照合ファイル名
                [
                    'ja' => 'mst_I18n_ja.data.decrypted',
                    'en' => 'mst_I18n_en.data.decrypted',
                    'zh-Hant' => 'mst_I18n_zh-Hant.data.decrypted',
                ],
                // アウトプットファイルの照合ファイル名
                [
                    'ja' => 'mst_I18n_ja.data.encrypted',
                    'en' => 'mst_I18n_en.data.encrypted',
                    'zh-Hant' => 'mst_I18n_zh-Hant.data.encrypted',
                ]
            ],
            '暗号化なし' => [
                // 非暗号化設定
                false,
                // 返却されるハッシュの照合ファイル名
                [
                    'ja' => 'mst_I18n_ja.data.decrypted',
                    'en' => 'mst_I18n_en.data.decrypted',
                    'zh-Hant' => 'mst_I18n_zh-Hant.data.decrypted',
                ],
                // アウトプットファイルの照合ファイル名
                [
                    'ja' => 'mst_I18n_ja.data.decrypted',
                    'en' => 'mst_I18n_en.data.decrypted',
                    'zh-Hant' => 'mst_I18n_zh-Hant.data.decrypted',
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider outputMessagePackAndJsonByI18nDataProvider
     */
    public function outputMessagePackAndJsonByI18n_MessagePackとJSONファイルを出力(
        bool $encrypt,
        array $expectedHashFileNameList,
        array $expectedEncryptedFileHashFileNameList,
    ): void {
        // Setup
        config([
            'wp_encryption.master_data_enabled' => $encrypt,
        ]);

        $targetMstEntities = [
            new DatabaseCsvEntity([
                // ヘッダ
                [
                    'release_key',
                    'id',
                    'mst_item_id',
                    'language',
                    'name',
                    'description',
                ],
                // データ
                [
                    '20230101',
                    'item1_ja',
                    'item1',
                    'ja',
                    'ドラゴンフルーツ',
                    'ドラゴンフルーツです',
                ],
                [
                    '20230101',
                    'item1_en',
                    'item1',
                    'en',
                    'Dragon Fruit(en)',
                    'This is a dragon fruit(en)',
                ],
                [
                    '20230101',
                    'item1_zh-Hans',
                    'item1',
                    'zh-Hant',
                    'Dragon Fruit(zh-Hant)',
                    'This is a dragon fruit(zh-Hant)',
                ],
            ], 'MstItemI18n'),
        ];
        $serializedFileDirPath = $this->testDirPath;
        $targetNameSpace = config('wp_master_asset_release_admin.clientMasterdataModelNameSpace.masteri18ndata');
        $target = MasterData::MASTERDATA_I18N;
        $targetPath = MasterData::MASTERDATA_I18N_PATH;
        // release_keyを別途設定
        foreach ($targetMstEntities as $targetMstEntity) {
            $targetMstEntity->setReleaseKey('20230101');
        }
        // 期待されるハッシュ値
        // 返ってくるハッシュ値は暗号化前のファイルハッシュとなる
        $expectedHashList = [];
        $expectedOutputFileHashList = [];
        foreach (['ja', 'en', 'zh-Hant'] as $language) {
            $expectedHashList[$language] = md5_file($this->testDataDirPath . DIRECTORY_SEPARATOR . $expectedHashFileNameList[$language]);
            $expectedOutputFileHashList[$language] = md5_file($this->testDataDirPath . DIRECTORY_SEPARATOR . $expectedEncryptedFileHashFileNameList[$language]);
        }

        // Exercise
        $hashList = $this->callMethod(
            $this->service,
            'outputMessagePackAndJsonByI18n',
            $targetMstEntities,
            $serializedFileDirPath,
            $targetNameSpace,
            $target,
            $targetPath,
        );

        // Verify
        // 取得するハッシュの確認 (圧縮前のファイルハッシュになる)
        $this->assertEquals($expectedHashList, $hashList);
        // 生成されたデータのハッシュチェック
        foreach (['ja', 'en', 'zh-Hant'] as $language) {
            $outputfile = implode(
                DIRECTORY_SEPARATOR,
                [$serializedFileDirPath, $targetPath, "mst_I18n_{$language}_{$hashList[$language]}.data"]
            );
            $outputFileHash = md5_file($outputfile);
            $this->assertEquals($expectedOutputFileHashList[$language], $outputFileHash);
        }
    }
}
