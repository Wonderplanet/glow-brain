<?php

declare(strict_types=1);

namespace Filament\Resources\MngAssetReleaseImportResource\Pages;

use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource\Pages\ImportMngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;
use WonderPlanet\Tests\TestCase;

class ImportMngAssetReleaseTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @test
     *
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        // Setup
        // develop環境へ接続しないようMockする
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getLatestReleaseKey')->andReturn(2024112901);
        $mock->shouldReceive('getImportEnvironment')->andReturn(['develop', 'qa']);
        $mock->shouldReceive('getEffectiveAssetReleaseList')->andReturn(collect([2024112901, 2024112801]));
        $fromEnvironmentReleaseKeyList = [
            [
                'release_key' => 2024112901,
                'target_release_version_id' => "12345",
            ],
            [
                'release_key' => 2024112802,
                'target_release_version_id' => "12345",
            ],
        ];
        $mock->shouldReceive('getEffectiveReleaseKeyListFromEnvironment')->andReturn(collect($fromEnvironmentReleaseKeyList));

        $importMngAssetRelease = $this->app->make(ImportMngAssetRelease::class);
        $this->setPrivateProperty($importMngAssetRelease, 'service', $mock);

        // Exercise
        // Verify
        Livewire::test(ImportMngAssetRelease::class)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function next_インポート元環境のバリデーションチェック(): void
    {
        // Setup
        // develop環境へ接続しないようMockする
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getLatestReleaseKey')->andReturn(2024112901);
        $mock->shouldReceive('getImportEnvironment')->andReturn(['develop', 'qa']);
        $mock->shouldReceive('getEffectiveAssetReleaseList')->andReturn(collect([2024112901, 2024112801]));
        $fromEnvironmentReleaseKeyList = [
            [
                'release_key' => 2024112901,
                'target_release_version_id' => "12345",
            ],
            [
                'release_key' => 2024112802,
                'target_release_version_id' => "12345",
            ],
        ];
        $mock->shouldReceive('getEffectiveReleaseKeyListFromEnvironment')->andReturn(collect($fromEnvironmentReleaseKeyList));

        $importMngAssetRelease = $this->app->make(ImportMngAssetRelease::class);
        $this->setPrivateProperty($importMngAssetRelease, 'service', $mock);

        // Exercise
        // Verify
        Livewire::test(ImportMngAssetRelease::class)
            ->call('next')
            ->assertHasErrors(['data.fromEnvironment' => 'required'])
            ->assertSee('インポート元環境の選択は必須です');
    }

    /**
     * @test
     */
    public function next_ReleaseKeyの選択バリデーションチェック(): void
    {
        // Setup
        Notification::fake(); // 通知のフェイク操作

        // develop環境へ接続しないようMockする
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getLatestReleaseKey')->andReturn(2024112901);
        $mock->shouldReceive('getImportEnvironment')->andReturn(['develop', 'qa']);
        $mock->shouldReceive('getEffectiveAssetReleaseList')->andReturn(collect([2024112901, 2024112801]));
        $fromEnvironmentReleaseKeyList = [
            [
                'release_key' => 2024112901,
                'description' => 'memo',
                'target_release_version_id' => "12345",
            ],
            [
                'release_key' => 2024112802,
                'description' => 'memo',
                'target_release_version_id' => "12345",
            ],
        ];
        $mock->shouldReceive('getEffectiveReleaseKeyListFromEnvironment')->andReturn(collect($fromEnvironmentReleaseKeyList));

        $importMngAssetRelease = $this->app->make(ImportMngAssetRelease::class);
        $this->setPrivateProperty($importMngAssetRelease, 'service', $mock);

        // Exercise
        // Verify
        Livewire::test(ImportMngAssetRelease::class)
            ->set('data.fromEnvironment', '1')
            ->call('next')
            ->assertNotified('リリースキーが選択されていません');
    }

    /**
     * @test
     */
    public function next_エラーなく次画面へ遷移できるか(): void
    {
        // Setup
        // develop環境へ接続しないようMockする
        $mock = $this->mock(MngAssetReleaseService::class);
        $mock->shouldReceive('getLatestReleaseKey')->andReturn(2024112901);
        $mock->shouldReceive('getImportEnvironment')->andReturn(['develop', 'qa']);
        $mock->shouldReceive('getEffectiveAssetReleaseList')->andReturn(collect([2024112901, 2024112801]));
        $fromEnvironmentReleaseKeyList = [
            [
                'release_key' => 2024112901,
                'description' => 'memo',
                'target_release_version_id' => "12345",
            ],
            [
                'release_key' => 2024112802,
                'description' => 'memo',
                'target_release_version_id' => "12345",
            ],
        ];
        $mock->shouldReceive('getEffectiveReleaseKeyListFromEnvironment')->andReturn(collect($fromEnvironmentReleaseKeyList));

        $importMngAssetRelease = $this->app->make(ImportMngAssetRelease::class);
        $this->setPrivateProperty($importMngAssetRelease, 'service', $mock);

        // Exercise
        // Verify
        Livewire::test(ImportMngAssetRelease::class)
            ->set('data.fromEnvironment', '1')
            ->set('data.releaseKeyIos', 2024112901)
            ->set('data.releaseKeyAndroid', 2024112901)
            ->call('next')
            ->assertHasNoFormErrors();
    }
}
