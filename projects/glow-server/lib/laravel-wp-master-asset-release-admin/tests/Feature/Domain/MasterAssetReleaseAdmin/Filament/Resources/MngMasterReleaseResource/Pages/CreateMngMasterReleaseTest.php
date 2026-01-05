<?php

declare(strict_types=1);

namespace Filament\Resources\MngMasterReleaseResource\Pages;

use Livewire\Livewire;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages\CreateMngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Tests\TestCase;

class CreateMngMasterReleaseTest extends TestCase
{
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        Livewire::test(CreateMngMasterRelease::class)
            ->assertSuccessful();
    }

    /**
     * @test
     * @dataProvider createData
     */
    public function create_作成ボタン実行チェック(string $input, string|null $expected): void
    {
        // Exercise
        Livewire::test(CreateMngMasterRelease::class)
            ->fillForm(['release_key' => 202409010, 'client_compatibility_version' => '1.0.0', 'description' => $input])
            ->call('create')
            ->assertHasNoFormErrors();

        // Verify
        //  作成ボタン実行後、入力したrelease_keyとclient_compatibility_versionが登録されているか
        $actual = MngMasterRelease::all()->first();
        $this->assertEquals(202409010, $actual->release_key);
        $this->assertEquals('1.0.0', $actual->client_compatibility_version);
        //  descriptionが想定の値になっているか
        $this->assertEquals($expected, $actual->description);
    }

    /**
     * @return array[]
     */
    private function createData(): array
    {
        return [
            'client_compatibility_version、メモが入力された' => ['テスト', 'テスト'],
            'client_compatibility_version入力、メモが未入力' => ['', null],
        ];
    }
    
    /**
     * @test
     */
    public function create_release_keyの重複バリデーションチェック(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                'id' => 'master-1',
                'release_key' => 2024090101,
                'client_compatibility_version' => '1.0.0',
            ]);
        
        // Exercise
        Livewire::test(CreateMngMasterRelease::class)
            ->fillForm(['release_key' => 2024090101, 'client_compatibility_version' => '1.0.1', 'description' => 'test'])
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
        MngMasterRelease::factory()
            ->create([
                'id' => 'master-1',
                'release_key' => 2024090101,
                'client_compatibility_version' => '1.0.0',
            ]);

        // Exercise
        Livewire::test(CreateMngMasterRelease::class)
            ->fillForm(['release_key' => 2024090102, 'client_compatibility_version' => $version, 'description' => 'test'])
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
