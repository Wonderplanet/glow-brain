<?php

declare(strict_types=1);

namespace Filament\Resources\MngAssetReleaseResource\Pages;

use Livewire\Livewire;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\MasterAssetReleaseConstants;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages\CreateMngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Tests\TestCase;

class CreateMngAssetReleaseTest extends TestCase
{
    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        Livewire::test(CreateMngAssetRelease::class)
            ->assertSuccessful();
    }

    /**
     * @test
     * @dataProvider createPlatformData
     */
    public function create_作成ボタン実行チェック_登録済みデータなし(int $platform): void
    {
        // Exercise
        Livewire::test(CreateMngAssetRelease::class)
            ->fillForm([
                'platform' => $platform,
                'release_key' => 202409010,
                'client_compatibility_version' => '0.0.1',
                'description' => 'メモテスト',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify
        //  作成ボタン実行後、入力した内容が登録されているか
        $actual = MngAssetRelease::all()->first();
        $this->assertEquals(202409010, $actual->release_key);
        $this->assertEquals($platform, $actual->platform);
        $this->assertEquals('0.0.1', $actual->client_compatibility_version);
        $this->assertEquals('メモテスト', $actual->description);
    }

    /**
     * @test
     * @dataProvider createPlatformData
     */
    public function create_作成ボタン実行チェック_登録済みデータあり(int $platform): void
    {
        // Setup
        MngAssetRelease::factory()
            ->createMany([
                [
                    // 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'enabled' => 1,
                    'target_release_version_id' => '100-ios',
                ],
                [
                    // 配信中(最古)
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'enabled' => 1,
                    'target_release_version_id' => '100-android',
                ],
                [
                    // 配信中(最新)
                    'release_key' => 202409011,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'enabled' => 1,
                    'target_release_version_id' => '101-ios',
                ],
                [
                    // 配信中(最新)
                    'release_key' => 202409011,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'enabled' => 1,
                    'target_release_version_id' => '101-android',
                ],
            ]);

        // Exercise
        Livewire::test(CreateMngAssetRelease::class)
            ->fillForm([
                'platform' => $platform,
                'release_key' => 202409012,
                'client_compatibility_version' => '0.0.1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify
        //  作成ボタン実行後、入力した内容が登録されているか
        $actual = MngAssetRelease::query()->where('release_key', 202409012)->first();
        $this->assertEquals(202409012, $actual->release_key);
        $this->assertEquals($platform, $actual->platform);
        $this->assertEquals('0.0.1', $actual->client_compatibility_version);
    }

    /**
     * @return array[]
     */
    private function createPlatformData(): array
    {
        return [
            'iosのみ' => [PlatformConstant::PLATFORM_IOS],
            'androidのみ' => [PlatformConstant::PLATFORM_ANDROID],
        ];
    }

    /**
     * @test
     */
    public function create_作成ボタン実行チェック_全プラットフォーム登録(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->createMany([
                [
                    // 配信中
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'enabled' => 1,
                    'target_release_version_id' => '100-ios',
                ],
                [
                    // 配信中
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'enabled' => 1,
                    'target_release_version_id' => '100-android',
                ],
            ]);

        // Exercise
        Livewire::test(CreateMngAssetRelease::class)
            ->fillForm([
                'platform' => MasterAssetReleaseConstants::PLATFORM_ALL,
                'release_key' => 202409011,
                'client_compatibility_version' => '0.0.1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify
        //  作成ボタン実行後、入力した内容が登録されているか
        $actuals = MngAssetRelease::query()->where('release_key', 202409011)->get();
        $this->assertCount(2, $actuals);
        $actualIos = $actuals->first(fn (MngAssetRelease $row) => $row->platform === PlatformConstant::PLATFORM_IOS);
        $this->assertEquals(202409011, $actualIos->release_key);
        $this->assertEquals(PlatformConstant::PLATFORM_IOS, $actualIos->platform);
        $this->assertEquals('0.0.1', $actualIos->client_compatibility_version);
        $actualAndroid = $actuals->first(fn (MngAssetRelease $row) => $row->platform === PlatformConstant::PLATFORM_ANDROID);
        $this->assertEquals(202409011, $actualAndroid->release_key);
        $this->assertEquals(PlatformConstant::PLATFORM_ANDROID, $actualAndroid->platform);
        $this->assertEquals('0.0.1', $actualAndroid->client_compatibility_version);
    }

    /**
     * @test
     */
    public function create_プルダウンにないプラットフォームが選択された(): void
    {
        // Exercise
        Livewire::test(CreateMngAssetRelease::class)
            ->fillForm([
                'platform' => 3,
                'release_key' => 202409011,
                'client_compatibility_version' => '0.0.1',
            ])
            ->call('create')
            // 想定しないplatformが選択された場合にエラーメッセージが表示されるか
            ->assertHasErrors(['data.platform' => ['想定しないplatformが選択されています']]);
    }

    /**
     * @test
     */
    public function create_release_keyの重複バリデーションチェック(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->createMany([
                [
                    // 配信中
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'enabled' => 1,
                    'target_release_version_id' => '100-ios',
                ],
                [
                    // 配信中
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'enabled' => 1,
                    'target_release_version_id' => '100-android',
                ],
            ]);
        
        // Exercise
        Livewire::test(CreateMngAssetRelease::class)
            ->fillForm([
                'platform' => MasterAssetReleaseConstants::PLATFORM_ALL,
                'release_key' => 202409010,
            ])
            ->call('create')
            ->assertHasErrors(['data.release_key' => ['すでに登録済みのrelease_keyです']]);
    }
    
    /**
     * @test
     * @dataProvider createValidationData
     */
    public function create_クライアント互換性バージョンバリデーションチェック(string $version, string $error): void
    {
        // Setup
        MngAssetRelease::factory()
            ->createMany([
                [
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_IOS,
                    'enabled' => 0,
                    'client_compatibility_version' => '1.0.0',
                ],
                [
                    'release_key' => 202409010,
                    'platform' => PlatformConstant::PLATFORM_ANDROID,
                    'enabled' => 0,
                    'client_compatibility_version' => '1.0.0',
                ],
            ]);
        
        // Exercise
        Livewire::test(CreateMngAssetRelease::class)
            ->fillForm([
                'platform' => MasterAssetReleaseConstants::PLATFORM_ALL,
                'release_key' => 2024090102,
                'client_compatibility_version' => $version,
            ])
            ->call('create')
            ->assertHasErrors(['data.client_compatibility_version' => [$error]]);
    }
    
    /**
     * @return array[]
     */
    private function createValidationData(): array
    {
        return [
            '最新より小さい入力だった' => ['0.0.9', '最新バージョン(1.0.0)より大きいバージョンを指定してください'],
            '使用できない文字が入力された' => ['v0.0.9', '「数字.数字.数字」の形式で入力してください'],
        ];
    }
}
