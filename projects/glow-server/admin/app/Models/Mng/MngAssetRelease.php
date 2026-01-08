<?php

namespace App\Models\Mng;

use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease as MngMngAssetRelease;

class MngAssetRelease extends MngMngAssetRelease
{
    public static function isUnreleasedByReleaseKey(int $releaseKey): bool
    {
        return self::query()
            ->where('release_key', $releaseKey)
            ->where('enabled', 0)
            ->exists();
    }

    public static function getUnreleasedList(): Collection
    {
        return self::query()
            ->where('enabled', 0)
            ->get();
    }

    /**
     * @return array<string, string>
     * key: release_key, value: release_key
     */
    public static function getReleaseKeys(): array
    {
        return self::query()
            ->select('release_key')
            ->distinct()
            ->pluck('release_key', 'release_key')
            ->toArray();
    }
}
