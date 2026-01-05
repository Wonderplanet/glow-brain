<?php

declare(strict_types=1);

namespace Filament\Resources\MngMasterReleaseResource\Widgets;

use Livewire\Livewire;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Widgets\MngMasterReleaseStatusOverview;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Tests\TestCase;

class MngMasterReleaseStatusOverviewTest extends TestCase
{
    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function canRender_配信中データがない場合警告文言が表示されること(): void
    {
        Livewire::test(MngMasterReleaseStatusOverview::class)
            ->assertSuccessful()
            ->assertSeeText('配信中のリリースデータが設定されていません。')
            ->assertSeeText('マスタデータ配信管理から配信設定を実施するか、環境構築ドキュメントに従ってコマンドを実施してください');
    }

    /**
     * @test
     */
    public function canRender_配信中データがある場合が配信中データが表示されること(): void
    {
        // Setup
        $mngMasterReleaseVersionId = 'version-1';
        $mngMasterRelease = MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'enabled' => 1,
                'target_release_version_id' => $mngMasterReleaseVersionId,
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => $mngMasterReleaseVersionId,
                'release_key' => '202301',
                'git_revision' => 'test1',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);

        // Exercise
        Livewire::test(MngMasterReleaseStatusOverview::class)
            ->assertSuccessful()
            ->assertSeeText("配信中のリリースキー : {$mngMasterRelease->release_key}")
            ->assertSeeText("配信中のdata hash : {$mngMasterRelease->mngMasterReleaseVersion->data_hash}");
    }
}
