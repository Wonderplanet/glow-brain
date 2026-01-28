<?php

declare(strict_types=1);

namespace Filament\Pages;

use Tests\Traits\ReflectionTrait;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Pages\MngMasterReleases\Diff;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Tests\TestCase;

class DiffTest extends TestCase
{
    use ReflectionTrait;

    // デフォルトのmng_master_releasesのデータを投入させない
    protected bool $ignoreDefaultCsvImport = true;

    /**
     * @test
     */
    public function setReleaseDiffData_表示データ生成チェック(): void
    {
        $diff = app()->make(Diff::class);
        $apply = MngMasterRelease::factory()
            ->create([
                // 配信中
                'id' => 'master-1',
                'release_key' => 2024090101,
                'enabled' => 1,
                'target_release_version_id' => '101',
                'description' => '2024年9月配信版'
            ]);
        $applyVersions = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '101',
                'release_key' => 2024090101,
            ]);
        $pending = MngMasterRelease::factory()
            ->create([
                // 配信準備中
                'id' => 'master-2',
                'release_key' => 2024090102,
                'enabled' => 0,
                'target_release_version_id' => '102',
                'description' => '2024年9月修正版'
            ]);
        $pendingVersions = MngMasterReleaseVersion::factory()
            ->create([
                'id' => '102',
                'release_key' => 2024090102,
            ]);
        $pendingNoVersions = MngMasterRelease::factory()
            ->create([
                // 配信準備中(リリースデータなし)
                'id' => 'master-3',
                'release_key' => 2024090103,
                'enabled' => 0,
            ]);

        $diff->applyMngMasterReleases = collect([$apply]);
        $diff->mngMasterReleasesByApplyOrPending = collect([$pending, $apply, $pendingNoVersions]);

        // Exercise
        $this->callMethod($diff, 'setReleaseDiffData');
        $actual = $diff->releaseDiffData;

        // Verify
        $actualApply = collect($actual)->first(fn ($row) => $row['releaseKey'] === $apply->release_key);
        $this->assertEquals([
            'releaseKey' => $apply->release_key,
            'status' => '配信中',
            'statusColor' => 'color: deeppink',
            'oldDataHash' => $applyVersions->data_hash,
            'newDataHash' => '新規データハッシュ',
            'description' => $apply->description,
            'oldGitRevision' =>  $applyVersions->git_revision,
            'newGitRevision' => '新規リビジョン',
        ], $actualApply);
        $actualPending = collect($actual)->first(fn ($row) => $row['releaseKey'] === $pending->release_key);
        $this->assertEquals([
            'releaseKey' => $pending->release_key,
            'status' => '配信準備中',
            'statusColor' => 'color: darkolivegreen',
            'oldDataHash' => $pendingVersions->data_hash,
            'newDataHash' => '新規データハッシュ',
            'description' => $pending->description,
            'oldGitRevision' => $pendingVersions->git_revision,
            'newGitRevision' => '新規リビジョン',
        ], $actualPending);
        $actualPendingNoVersions = collect($actual)->first(fn ($row) => $row['releaseKey'] === $pendingNoVersions->release_key);
        $this->assertEquals([
            'releaseKey' => $pendingNoVersions->release_key,
            'status' => '配信準備中',
            'statusColor' => 'color: darkolivegreen',
            'oldDataHash' => '(設定なし)',
            'newDataHash' => '新規データハッシュ',
            'description' => $pendingNoVersions->description,
            'oldGitRevision' => '(設定なし)',
            'newGitRevision' => '新規リビジョン',
        ], $actualPendingNoVersions);
    }
}
