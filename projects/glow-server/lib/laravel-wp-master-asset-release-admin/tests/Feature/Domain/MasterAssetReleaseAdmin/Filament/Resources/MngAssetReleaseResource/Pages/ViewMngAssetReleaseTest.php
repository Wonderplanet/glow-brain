<?php

declare(strict_types=1);

namespace Filament\Resources\MngAssetReleaseResource\Pages;

use Livewire\Livewire;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages\ViewMngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetReleaseVersion;
use WonderPlanet\Tests\TestCase;

class ViewMngAssetReleaseTest extends TestCase
{
    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
            ]);

        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function delete_削除チェック(): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
                'target_release_version_id' => '101',
            ]);
        MngAssetReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024100101,
            ]);

        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->callAction('deleteButton');

        // Verify
        // MngAssetReleaseのデータが削除されているか
        $mngAssetReleases = MngAssetRelease::all();
        $this->assertCount(0, $mngAssetReleases);

        // MngAssetReleaseVersionのデータが削除されているか
        $mngAssetReleaseVersions = MngAssetReleaseVersion::all();
        $this->assertCount(0, $mngAssetReleaseVersions);
    }

    /**
     * @test
     */
    public function delete_MngAssetReleaseのみデータがなかった時の削除チェック(): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
            ]);

        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->callAction('deleteButton');

        // Verify
        // MngAssetReleaseのデータが削除されているか
        $mngAssetReleases = MngAssetRelease::all();
        $this->assertCount(0, $mngAssetReleases);
    }

    /**
     * @test
     * @dataProvider updateDescriptionData
     */
    public function update_更新チェック(string|null $default, string $input, string|null $expected): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'description' => $default,
            ]);

        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->fillForm([
                'clientCompatibilityVersion' => '1.0.0',
                'description' => $input,
            ])
            ->call('update');

        // Verify
        // MngAssetReleaseが更新されているか
        $mngAssetRelease = MngAssetRelease::query()
            ->find('release_1');
        $this->assertEquals('1.0.0', $mngAssetRelease->client_compatibility_version);
        $this->assertEquals($expected, $mngAssetRelease->description);
    }

    /**
     * @return array[]
     */
    private function updateDescriptionData(): array
    {
        return [
            // null状態で文字を入力して更新されているか
            '通常更新' => [null, 'ユニットテスト更新', 'ユニットテスト更新'],
            // 文字が入力されている状態で空文字を入力してnullとして更新されているか
            'null更新' => ['ユニットテスト更新', '', null],
        ];
    }

    /**
     * @test
     * @dataProvider updateInputData
     */
    public function update_クライアント互換性バージョン更新チェック(string $input): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'client_compatibility_version' => '0.0.1',
            ]);
        
        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->fillForm([
                'clientCompatibilityVersion' => $input,
            ])
            ->call('update');

        // Verify
        // MngMasterRelease.client_compatibility_versionが更新されているか
        $mngMasterRelease = MngAssetRelease::query()
            ->find('release_1');
        $this->assertEquals($input, $mngMasterRelease->client_compatibility_version);
    }

    /**
     * @return array[]
     */
    private function updateInputData(): array
    {
        return [
            '正常入力1' => ['1.0.0'],
            '正常入力2' => ['10.1000.100'],
        ];
    }
    
    /**
     * @test
     */
    public function update_クライアント互換性バージョン未入力バリデーションチェック(): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'client_compatibility_version' => '0.0.1',
            ]);

        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->fillForm([
                'clientCompatibilityVersion' => '',
            ])
            ->call('update')
            // クライアント互換性バージョンフォームを空文字で更新した場合に、必須入力バリデーションが実行されるか
            ->assertHasErrors(['clientCompatibilityVersion' => 'required']);
    }

    /**
     * @test
     * @dataProvider updateValidationData
     */
    public function update_クライアント互換性バージョン入力値バリデーションチェック(string $input): void
    {
        // Setup
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'client_compatibility_version' => '0.0.1',
            ]);
        
        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_1'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->fillForm(['clientCompatibilityVersion' => $input])
            ->call('update')
            // クライアント互換性バージョンフォームで数字と.以外を含めて更新した場合に、入力値バリデーションが実行されるか
            ->assertHasErrors(['clientCompatibilityVersion' => 'regex']);
    }

    /**
     * @return array[]
     */
    private function updateValidationData(): array
    {
        return [
            '文字が含まれている1' => ['v1.0.0'],
            '文字が含まれている2' => ['1.0.a'],
            '形式が異なる1' => ['1.0.0.0'],
            '形式が異なる2' => ['1.0.'],
            '形式が異なる3' => ['1.0.0.'],
        ];
    }
    
    /**
     * @test
     */
    public function update_クライアント互換性バージョンバリデーション最新より小さい入力値のチェック(): void
    {
        // Setup
        MngAssetRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 202410010,
                'client_compatibility_version' => '1.0.0',
            ]);
        $mngAssetRelease = MngAssetRelease::factory()
            ->create([
                'id' => 'release_2',
                'release_key' => 202410011,
                'client_compatibility_version' => '1.0.1',
            ]);

        // Exercise
        Livewire::test(ViewMngAssetRelease::class, ['record' => 'release_2'])
            ->set('mngAssetRelease', $mngAssetRelease)
            ->fillForm(['clientCompatibilityVersion' => '0.0.9'])
            ->call('update')
            // クライアント互換性バージョンフォームで数字と.以外を含めて更新した場合に、入力値バリデーションが実行されるか
            ->assertHasErrors(['clientCompatibilityVersion' => ['最新バージョン(1.0.0)より大きいバージョンを指定してください']]);
    }
}
