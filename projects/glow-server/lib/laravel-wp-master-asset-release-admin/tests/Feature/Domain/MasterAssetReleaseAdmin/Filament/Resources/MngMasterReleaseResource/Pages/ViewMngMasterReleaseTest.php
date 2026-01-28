<?php

declare(strict_types=1);

namespace Filament\Resources\MngMasterReleaseResource\Pages;

use Livewire\Livewire;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages\ViewMngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Tests\TestCase;

class ViewMngMasterReleaseTest extends TestCase
{
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    private MasterDataDBOperator $masterDataDBOperator;

    public function setUp(): void
    {
        parent::setUp();

        $this->masterDataDBOperator = app(MasterDataDBOperator::class);
    }

    /**
     * @test
     */
    public function canRender_画面の表示でエラーにならないこと(): void
    {
        // Setup
        MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
            ]);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function delete_削除チェック(): void
    {
        // Setup
        $releaseKey = '2024100101';
        $serverDbHash = 'abcd1234';
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => $releaseKey,
                'target_release_version_id' => '101',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => $releaseKey,
                'server_db_hash' => $serverDbHash,
            ]);
        $serverDbHashMap = [$releaseKey => $serverDbHash];
        $masterDbName = $this->masterDataDBOperator
            ->getMasterDbName($releaseKey, $serverDbHashMap);
        $this->masterDataDBOperator->create($masterDbName);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->set('mngMasterRelease', $mngMasterRelease)
            ->callAction('deleteButton');

        // Verify
        // マスターDBが削除されているか
        $databases = $this->masterDataDBOperator->showDatabases();
        $this->assertNotContains($masterDbName, $databases);

        // MngMasterReleaseのデータが削除されているか
        $mngMasterReleases = MngMasterRelease::all();
        $this->assertCount(0, $mngMasterReleases);

        // MngMasterReleaseVersionのデータが削除されているか
        $mngMasterReleaseVersions = MngMasterReleaseVersion::all();
        $this->assertCount(0, $mngMasterReleaseVersions);
    }

    /**
     * @test
     */
    public function delete_MngMasterReleaseのみデータがなかった時の削除チェック(): void
    {
        // Setup
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
            ]);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->set('mngMasterRelease', $mngMasterRelease)
            ->callAction('deleteButton');

        // Verify
        // MngMasterReleaseのデータが削除されているか
        $mngMasterReleases = MngMasterRelease::all();
        $this->assertCount(0, $mngMasterReleases);
    }

    /**
     * @test
     * @dataProvider updateInputData
     */
    public function update_クライアント互換性バージョン更新チェック(string $input): void
    {
        // Setup
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
                'client_compatibility_version' => '0.0.1',
            ]);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->set('mngMasterRelease', $mngMasterRelease)
            ->fillForm(['clientCompatibilityVersion' => $input])
            ->call('update');

        // Verify
        // MngMasterRelease.client_compatibility_versionが更新されているか
        $mngMasterRelease = MngMasterRelease::query()
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
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
                'client_compatibility_version' => '0.0.1',
            ]);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->set('mngMasterRelease', $mngMasterRelease)
            ->fillForm(['clientCompatibilityVersion' => ''])
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
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
                'client_compatibility_version' => '0.0.1',
            ]);
        
        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->set('mngMasterRelease', $mngMasterRelease)
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
        MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
                'client_compatibility_version' => '1.0.0',
            ]);
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_2',
                'release_key' => 2024100102,
                'client_compatibility_version' => '1.0.1',
            ]);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_2'])
            ->set('mngMasterRelease', $mngMasterRelease)
            ->fillForm(['clientCompatibilityVersion' => '0.0.9'])
            ->call('update')
            // クライアント互換性バージョンフォームで数字と.以外を含めて更新した場合に、入力値バリデーションが実行されるか
            ->assertHasErrors(['clientCompatibilityVersion' => ['最新バージョン(1.0.0)より大きいバージョンを指定してください']]);
    }

    /**
     * @test
     * @dataProvider updateDescriptionData
     */
    public function update_メモ更新チェック(string|null $default, string $input, string|null $expected): void
    {
        // Setup
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'id' => 'release_1',
                'release_key' => 2024100101,
                'client_compatibility_version' => '1.0.0',
                'description' => $default,
            ]);

        // Exercise
        Livewire::test(ViewMngMasterRelease::class, ['record' => 'release_1'])
            ->set('mngMasterRelease', $mngMasterRelease)
            ->set('description', $input)
            ->call('update');

        // Verify
        // MngMasterRelease.descriptionが更新されているか
        $mngMasterRelease = MngMasterRelease::query()
            ->find('release_1');
        $this->assertEquals($expected, $mngMasterRelease->description);
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
}
