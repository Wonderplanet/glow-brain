<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\OprAssetReleaseVersionEntity;
use App\Domain\Resource\Mst\Models\OprAssetReleaseVersion;

class OprAssetReleaseVersionRepository
{
    /**
     * 現在有効なアセットリリース情報取得
     *
     * @param int $platform
     * @param string $clientVersion
     *
     * @return OprAssetReleaseVersionEntity|null
     */
    public function getCurrent(int $platform, string $clientVersion): ?OprAssetReleaseVersionEntity
    {
        return OprAssetReleaseVersion::query()
            ->select('opr_asset_release_versions.*')
            ->join(
                'opr_asset_releases',
                'opr_asset_releases.target_release_version_id',
                '=',
                'opr_asset_release_versions.id'
            )
            // opr_asset_releasesから、現在有効なアセット情報を特定する
            ->whereNotNull('opr_asset_releases.target_release_version_id')
            ->where([
                'opr_asset_releases.platform' => $platform,
                'opr_asset_releases.enabled' => true,
                'opr_asset_release_versions.build_client_version' => $clientVersion,
            ])
            // release_keyが一番高い最初の1件
            ->orderBy('opr_asset_releases.release_key', 'desc')
            ->limit(1)
            ->first()
            ->toEntity();
    }
}
