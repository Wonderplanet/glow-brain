<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\Contracts\OprAssetReleaseControlInterface;
use App\Domain\Resource\Mst\Models\OprAssetReleaseControl;
use Carbon\CarbonImmutable;

class OprAssetReleaseControlRepository
{
    /**
     * @param CarbonImmutable $now
     * @param int $platform
     * @param string|null $version
     * @return OprAssetReleaseControlInterface|null
     */
    public function getCurrent(
        CarbonImmutable $now = new CarbonImmutable(),
        int $platform = 0,
        ?string $version = null,
    ): ?OprAssetReleaseControlInterface {
        $activeReleaseControl = OprAssetReleaseControl::query()
            ->when(isset($version), function ($query) use ($version) {
                return $query->where('version', $version);
            })
            ->where('platform', $platform)
            ->where('release_at', '<=', $now)
            ->orderBy('release_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $activeReleaseControl;
    }
}
