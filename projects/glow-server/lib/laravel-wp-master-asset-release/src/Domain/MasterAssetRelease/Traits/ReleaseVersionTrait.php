<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Traits;

use Illuminate\Support\Collection;

trait ReleaseVersionTrait
{
    /**
     * `client_compatibility_version`が同一バージョンだった場合に
     * `release_key`と`create_at`を比較して新しいバージョンデータを取得する
     *
     * @param Collection<int, mixed> $currentVersion
     * @param Collection<int, mixed> $targetVersion
     * @return Collection<int, mixed>
     */
    public function getLatestByVersion(
        Collection $currentVersion,
        Collection $targetVersion
    ): Collection {
        // リリースキーを優先して比較
        if ($currentVersion['entity']->getReleaseKey() !== $targetVersion['entity']->getReleaseKey()) {
            return $currentVersion['entity']->getReleaseKey() > $targetVersion['entity']->getReleaseKey()
                ? $currentVersion : $targetVersion;
        }

        // リリースキーも同一の場合は、作成日時を比較
        return $currentVersion['created_at'] > $targetVersion['created_at'] ? $currentVersion : $targetVersion;
    }
}
