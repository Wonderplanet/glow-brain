<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdminCacheService
{
    private function getAdmPermissionFeatureCacheKey(string $permissionId): string
    {
        return "adm_permission_feature_{$permissionId}";
    }

    public function getAdmPermissionFeature(string $permissionId): ?Collection
    {
        $cacheKey = $this->getAdmPermissionFeatureCacheKey($permissionId);
        return Cache::store('file')->get($cacheKey);
    }

    public function putAdmPermissionFeature(string $permissionId, Collection $features): void
    {
        $cacheKey = $this->getAdmPermissionFeatureCacheKey($permissionId);
        Cache::store('file')->put($cacheKey, $features);
    }

    public function deleteAdmPermissionFeature(string $permissionId): void
    {
        $cacheKey = $this->getAdmPermissionFeatureCacheKey($permissionId);
        Cache::store('file')->delete($cacheKey);
    }

    private function getOcarinaErrorCacheKey(string $dbName): string
    {
        return "ocarina_error_{$dbName}";
    }

    public function getOcarinaError(string $dbName): ?bool
    {
        $cacheKey = $this->getOcarinaErrorCacheKey($dbName);
        return Cache::store('file')->get($cacheKey);
    }

    public function putOcarinaError(string $dbName, bool $hasError): void
    {
        $cacheKey = $this->getOcarinaErrorCacheKey($dbName);
        Cache::store('file')->put($cacheKey, $hasError);
    }

    public function deleteOcarinaError(string $dbName): void
    {
        $cacheKey = $this->getOcarinaErrorCacheKey($dbName);
        Cache::store('file')->delete($cacheKey);
    }
}
