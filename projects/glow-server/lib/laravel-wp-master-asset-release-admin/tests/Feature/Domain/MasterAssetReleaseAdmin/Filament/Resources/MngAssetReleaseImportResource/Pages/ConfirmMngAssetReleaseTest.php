<?php

declare(strict_types=1);

namespace Filament\Resources\MngAssetReleaseImportResource\Pages;

use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Mockery;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Enums\AssetDiffType;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource\Pages\ConfirmMngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;
use WonderPlanet\Tests\TestCase;

class ConfirmMngAssetReleaseTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        // Setup
        $assetInfo = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => "",
        ]);
        // s3 diffをしているため、S3に接続しないようにモック
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getAssetConfigNameBoth')->andReturn(['input' => '', 'output' => '']);
        $mock->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')->andReturn($assetInfo);
        $mock->shouldReceive('getAllAssetFiles')->andReturn(collect([]));

        Livewire::test(
            ConfirmMngAssetRelease::class,
            ['fromEnvironment' => 'develop']
        )
        ->assertSuccessful();
    }

    /**
     * @test
     */
    public function setDiff_差分情報セット時にエラーにならないこと_両OS選択(): void
    {
        // Setup
        $directoryInput = '/assetbundles/android/0142b33fd550fcf7dcf90fc951bf65e4266f9c26/';
        $allFilesInput = collect([
            'avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle' =>
            [
                'file' => 'avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle',
                'size' => 36958,
                'size_format' => '36.09 KB',
            ],
            'avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle' =>
            [
                'file' => 'avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle',
                'size' => 241056,
                'size_format' => '235.41 KB',
            ],
            'catalog_1.data' =>
            [
                'file' => 'catalog_1.data',
                'size' => 27334,
                'size_format' => '26.69 KB',
            ],
            'catalog_1.hash' =>
            [
                'file' => 'catalog_1.hash',
                'size' => 34,
                'size_format' => '34.00 B',
            ],
            'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle' =>
            [
                'file' => 'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle',
                'size' => 11398420,
                'size_format' => '10.87 MB',
            ],
        ]);
        $assetInfoInput = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => $directoryInput,
            'release_version' => [],
        ]);
        $directoryOutput = '/assetbundles/android/047f5febfaf459ecaaf14a8c3013769b4a0ab317/';
        $allFilesOutput = collect([
            'avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle' =>
            [
                'file' => 'avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle',
                'size' => 9232,
                'size_format' => '9.02 KB',
            ],
            'catalog_1.data' =>
            [
                'file' => 'catalog_1.data',
                'size' => 17617,
                'size_format' => '17.20 KB',
            ],
            'catalog_1.hash' =>
            [
                'file' => 'catalog_1.hash',
                'size' => 34,
                'size_format' => '34.00 B',
            ],
            'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle' =>
            [
                'file' => 'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle',
                'size' => 11398420,
                'size_format' => '10.87 MB',
            ],
            'demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle' =>
            [
                'file' => 'demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle',
                'size' => 5387084,
                'size_format' => '5.14 MB',
            ],
        ]);
        $assetInfoOutPut = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => $directoryOutput,
            'release_version' => [],
        ]);
        $configs = [
            'input' => '',
            'output' => '',
        ];

        // S3に接続しないようにモック
        $mngAssetReleaseServiceMock = Mockery::mock(MngAssetReleaseService::class);
        $mngAssetReleaseServiceMock
            ->shouldReceive('getAllAssetFiles')
            ->andReturnUsing(function ($config, $directory) use ($directoryInput, $directoryOutput, $allFilesInput, $allFilesOutput) {
                if ($directory === $directoryInput) {
                    return $allFilesInput;
                } else {
                    return $allFilesOutput;
                }
            });
        $mngAssetReleaseServiceMock
            ->shouldReceive('getAssetConfigNameBoth')
            ->andReturn($configs);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->with('develop', 1, 12345678)
            ->andReturn($assetInfoInput);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->with('develop', 2, 12345678)
            ->andReturn($assetInfoInput);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->with('testing', 1, 12345678)
            ->andReturn($assetInfoOutPut);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->with('testing', 2, 12345678)
            ->andReturn($assetInfoOutPut);

        $confirmMngAssetRelease = $this->app->make(ConfirmMngAssetRelease::class);
        $this->setPrivateProperty($confirmMngAssetRelease, 'mngAssetReleaseService', $mngAssetReleaseServiceMock);
        // 両OSの選択したreleaseKeyを設定
        $this->setPrivateProperty($confirmMngAssetRelease, 'releaseKeyAndroid', 12345678);
        $this->setPrivateProperty($confirmMngAssetRelease, 'releaseKeyIos', 12345678);

        // Exercise
        $this->callMethod(
            $confirmMngAssetRelease,
            'setDiff',
            'develop', 'testing'
        );

        // Verify
        $diffAndroid = $this->getProperty($confirmMngAssetRelease, 'diffAndroid');
        // 差分の内容が正しいか
        $this->assertFalse($diffAndroid->isEmpty());
        $diff = $diffAndroid->get(0);
        $this->assertEquals('avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_ADD, $diff['diff_type']);
        $diff = $diffAndroid->get(1);
        $this->assertEquals('avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_DELETE, $diff['diff_type']);
        $diff = $diffAndroid->get(2);
        $this->assertEquals('avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_ADD, $diff['diff_type']);
        $diff = $diffAndroid->get(3);
        $this->assertEquals('catalog_1.data', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_CHANGE, $diff['diff_type']);
        $diff = $diffAndroid->get(4);
        $this->assertEquals('demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_DELETE, $diff['diff_type']);

        // 差分の内容が正しいか - iOS
        $diffIos = $this->getProperty($confirmMngAssetRelease, 'diffIos');
        $this->assertFalse($diffIos->isEmpty());
        $diff = $diffIos->get(0);
        $this->assertEquals('avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_ADD, $diff['diff_type']);
        $diff = $diffIos->get(1);
        $this->assertEquals('avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_DELETE, $diff['diff_type']);
        $diff = $diffIos->get(2);
        $this->assertEquals('avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_ADD, $diff['diff_type']);
        $diff = $diffIos->get(3);
        $this->assertEquals('catalog_1.data', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_CHANGE, $diff['diff_type']);
        $diff = $diffIos->get(4);
        $this->assertEquals('demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_DELETE, $diff['diff_type']);

        // 差分の件数チェック
        $diffCountAndroid = $this->getProperty($confirmMngAssetRelease, 'androidDiffCount')->toArray();
        $this->assertEquals(2, $diffCountAndroid['deleteCount']);
        $this->assertEquals(2, $diffCountAndroid['newCount']);
        $this->assertEquals(1, $diffCountAndroid['changeCount']);
        $diffCountIos = $this->getProperty($confirmMngAssetRelease, 'iosDiffCount')->toArray();
        $this->assertEquals(2, $diffCountIos['deleteCount']);
        $this->assertEquals(2, $diffCountIos['newCount']);
        $this->assertEquals(1, $diffCountIos['changeCount']);

    }

    /**
     * @test
     */
    public function setDiff_差分情報セット時にエラーにならないこと_片方のOS選択(): void
    {
        // Setup
        $directoryInput = '/assetbundles/android/0142b33fd550fcf7dcf90fc951bf65e4266f9c26/';
        $allFilesInput = collect([
            'avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle' =>
                [
                    'file' => 'avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle',
                    'size' => 36958,
                    'size_format' => '36.09 KB',
                ],
            'avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle' =>
                [
                    'file' => 'avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle',
                    'size' => 241056,
                    'size_format' => '235.41 KB',
                ],
            'catalog_1.data' =>
                [
                    'file' => 'catalog_1.data',
                    'size' => 27334,
                    'size_format' => '26.69 KB',
                ],
            'catalog_1.hash' =>
                [
                    'file' => 'catalog_1.hash',
                    'size' => 34,
                    'size_format' => '34.00 B',
                ],
            'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle' =>
                [
                    'file' => 'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle',
                    'size' => 11398420,
                    'size_format' => '10.87 MB',
                ],
        ]);
        $assetInfoInput = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => $directoryInput,
            'release_version' => [],
        ]);
        $directoryOutput = '/assetbundles/android/047f5febfaf459ecaaf14a8c3013769b4a0ab317/';
        $allFilesOutput = collect([
            'avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle' =>
                [
                    'file' => 'avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle',
                    'size' => 9232,
                    'size_format' => '9.02 KB',
                ],
            'catalog_1.data' =>
                [
                    'file' => 'catalog_1.data',
                    'size' => 17617,
                    'size_format' => '17.20 KB',
                ],
            'catalog_1.hash' =>
                [
                    'file' => 'catalog_1.hash',
                    'size' => 34,
                    'size_format' => '34.00 B',
                ],
            'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle' =>
                [
                    'file' => 'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle',
                    'size' => 11398420,
                    'size_format' => '10.87 MB',
                ],
            'demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle' =>
                [
                    'file' => 'demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle',
                    'size' => 5387084,
                    'size_format' => '5.14 MB',
                ],
        ]);
        $assetInfoOutPut = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => $directoryOutput,
            'release_version' => [],
        ]);
        $configs = [
            'input' => '',
            'output' => '',
        ];

        // S3に接続しないようにモック
        $mngAssetReleaseServiceMock = Mockery::mock(MngAssetReleaseService::class);
        $mngAssetReleaseServiceMock
            ->shouldReceive('getAllAssetFiles')
            ->andReturnUsing(function ($config, $directory) use ($directoryInput, $directoryOutput, $allFilesInput, $allFilesOutput) {
                if ($directory === $directoryInput) {
                    return $allFilesInput;
                } else {
                    return $allFilesOutput;
                }
            });
        $mngAssetReleaseServiceMock
            ->shouldReceive('getAssetConfigNameBoth')
            ->andReturn($configs);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->with('develop', 1, 12345678)
            ->andReturn($assetInfoInput);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->with('testing', 1, 12345678)
            ->andReturn($assetInfoOutPut);

        $confirmMngAssetRelease = $this->app->make(ConfirmMngAssetRelease::class);
        $this->setPrivateProperty($confirmMngAssetRelease, 'mngAssetReleaseService', $mngAssetReleaseServiceMock);
        // iOSのみreleaseKeyを選択
        $this->setPrivateProperty($confirmMngAssetRelease, 'releaseKeyIos', 12345678);

        // Exercise
        $this->callMethod(
            $confirmMngAssetRelease,
            'setDiff',
            'develop', 'testing'
        );

        // Verify
        $diffIos = $this->getProperty($confirmMngAssetRelease, 'diffIos');
        // 差分の内容が正しいか
        $this->assertFalse($diffIos->isEmpty());
        $diff = $diffIos->get(0);
        $this->assertEquals('avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_ADD, $diff['diff_type']);
        $diff = $diffIos->get(1);
        $this->assertEquals('avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_DELETE, $diff['diff_type']);
        $diff = $diffIos->get(2);
        $this->assertEquals('avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_ADD, $diff['diff_type']);
        $diff = $diffIos->get(3);
        $this->assertEquals('catalog_1.data', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_CHANGE, $diff['diff_type']);
        $diff = $diffIos->get(4);
        $this->assertEquals('demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle', $diff['file']);
        $this->assertEquals(AssetDiffType::DIFF_TYPE_DELETE, $diff['diff_type']);
    }

    /**
     * @test
     */
    public function setDiff_差分情報セット時にインポート元環境のデータが取得失敗(): void
    {
        // Setup
        Notification::fake();
        $directoryInput = '/assetbundles/android/0142b33fd550fcf7dcf90fc951bf65e4266f9c26/';
        $allFilesInput = collect([
            'avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle' =>
                [
                    'file' => 'avatar-frame_assets_all_a7388afcbc619778193fd855a33b934d.bundle',
                    'size' => 36958,
                    'size_format' => '36.09 KB',
                ],
            'avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle' =>
                [
                    'file' => 'avatar-icon_assets_all_d4ad0e06efd0a54cd7bcd04df6100d17.bundle',
                    'size' => 241056,
                    'size_format' => '235.41 KB',
                ],
            'catalog_1.data' =>
                [
                    'file' => 'catalog_1.data',
                    'size' => 27334,
                    'size_format' => '26.69 KB',
                ],
            'catalog_1.hash' =>
                [
                    'file' => 'catalog_1.hash',
                    'size' => 34,
                    'size_format' => '34.00 B',
                ],
            'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle' =>
                [
                    'file' => 'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle',
                    'size' => 11398420,
                    'size_format' => '10.87 MB',
                ],
        ]);
        $assetInfoInput = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => $directoryInput,
            'release_version' => [],
        ]);
        $directoryOutput = '/assetbundles/android/047f5febfaf459ecaaf14a8c3013769b4a0ab317/';
        $allFilesOutput = collect([
            'avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle' =>
                [
                    'file' => 'avatar-icon_assets_all_632052f7bcb3ca11d9db7d4f7126d214.bundle',
                    'size' => 9232,
                    'size_format' => '9.02 KB',
                ],
            'catalog_1.data' =>
                [
                    'file' => 'catalog_1.data',
                    'size' => 17617,
                    'size_format' => '17.20 KB',
                ],
            'catalog_1.hash' =>
                [
                    'file' => 'catalog_1.hash',
                    'size' => 34,
                    'size_format' => '34.00 B',
                ],
            'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle' =>
                [
                    'file' => 'demo-movie-1_assets_demo_movie_sprint10_3f93940f025f73e049dfcb0c5ad60fe3.bundle',
                    'size' => 11398420,
                    'size_format' => '10.87 MB',
                ],
            'demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle' =>
                [
                    'file' => 'demo-movie-1_assets_demo_movie_sprint7_3110e6607a861da6b44fa27fd80ed17f.bundle',
                    'size' => 5387084,
                    'size_format' => '5.14 MB',
                ],
        ]);
        $assetInfoOutPut = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "aaa",
            ],
            'path' => $directoryOutput,
            'release_version' => [],
        ]);
        $configs = [
            'input' => '',
            'output' => '',
        ];

        // S3に接続しないようにモック
        $mngAssetReleaseServiceMock = Mockery::mock(MngAssetReleaseService::class);
        $mngAssetReleaseServiceMock
            ->shouldReceive('getAllAssetFiles')
            ->andReturnUsing(function ($config, $directory) use ($directoryInput, $directoryOutput, $allFilesInput, $allFilesOutput) {
                if ($directory === $directoryInput) {
                    return $allFilesInput;
                } else {
                    return $allFilesOutput;
                }
            });
        $mngAssetReleaseServiceMock
            ->shouldReceive('getAssetConfigNameBoth')
            ->andReturn($configs);
        $mngAssetReleaseServiceMock
            ->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')
            ->andThrow(\Exception::class, 'test');

        $confirmMngAssetRelease = $this->app->make(ConfirmMngAssetRelease::class);
        $this->setPrivateProperty($confirmMngAssetRelease, 'mngAssetReleaseService', $mngAssetReleaseServiceMock);
        // iOSのみreleaseKeyを選択
        $this->setPrivateProperty($confirmMngAssetRelease, 'releaseKeyIos', 12345678);

        // Exercise
        // Verify
        Livewire::test(
            ConfirmMngAssetRelease::class,
            ['fromEnvironment' => 'develop', 'releaseKeyIos' => 12345678]
        )->assertRedirect(MngAssetReleaseImportResource::getUrl('import'))
            ->assertNotified('アセット環境間インポートを実行できません。');
    }

    /**
     * @test
     */
    public function submit_インポート実行が成功すること(): void
    {
        // Setup
        Notification::fake();
        // S3に接続しないようにモック
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getAssetConfigNameBoth')->andReturn(['input' => '', 'output' => '']);
        $mock->shouldReceive('assetImport');
        $mock->shouldReceive('insertReleaseVersionAndUpdateTargetId');
        $assetInfo = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "memo",
            ],
            'path' => "",
            'release_version' => collect(['catalog_hash' => 'test']),
        ]);
        $mock->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')->andReturn($assetInfo);
        $mock->shouldReceive('getAllAssetFiles')->andReturn(collect([]));

        // Exercise
        // Verify
        Livewire::test(
            ConfirmMngAssetRelease::class,
            ['fromEnvironment' => 'develop', 'releaseKeyIos' => 12345678]
        )
            ->call('importAsset')
            ->assertRedirect(MngAssetReleaseImportResource::getUrl('list'))
            ->assertHasNoFormErrors()
            ->assertNotified('インポートが成功しました。');
    }

    /**
     * @test
     */
    public function submit_エラーでインポートが失敗した場合(): void
    {
        // Setup
        Notification::fake();
        // S3に接続しないようにモック
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getAssetConfigNameBoth')->andReturn(['input' => '', 'output' => '']);
        $mock->shouldReceive('assetImport');
        $mock->shouldReceive('insertReleaseVersionAndUpdateTargetId')->andThrow(\Exception::class, 'test');
        $assetInfo = collect([
            'asset_info' => [
                'platform' => 1,
                'release_key' => 12345678,
                'status' => 1,
                'git_revision' => "aaa",
                'catalog_hash' => "bbb",
                'description' => "memo",
            ],
            'path' => "",
            'release_version' => collect(['catalog_hash' => 'test']),
        ]);
        $mock->shouldReceive('createAssetFileDirectoryPathAndGetAssetInfo')->andReturn($assetInfo);
        $mock->shouldReceive('getAllAssetFiles')->andReturn(collect([]));

        // Exercise
        // Verify
        Livewire::test(
            ConfirmMngAssetRelease::class,
            ['fromEnvironment' => 'develop', 'releaseKeyIos' => 12345678]
        )
            ->call('importAsset')
            ->assertNotified('アセット環境間インポートに失敗しました。');
    }
}
