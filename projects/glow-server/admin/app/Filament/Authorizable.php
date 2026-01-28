<?php

namespace App\Filament;

use App\Models\Adm\AdmPermissionFeature;
use App\Services\AdminCacheService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Filament\Resources\Resourceのcanメソッドに実装されている認可処理を上書きするために使用することを想定
 * Laravel標準のポリシーベースのものから、Laravel-permissionsを用いたロールベースのロジックに差し替える
 */
trait Authorizable
{
    public static function isAdmin(): bool
    {
        $user = Filament::auth()->user();
        // Admin権限を持っているか
        $roles = $user->roles()->pluck('name');
        if ($roles->contains('Admin')) {
            return true;
        }
        return false;
    }

    public static function isLeader(): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $user = Filament::auth()->user();
        $roles = $user->roles()->pluck('name');
        if ($roles->contains('Leader')) {
            return true;
        }

        return false;
    }

    public static function canAccess(): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $user = Filament::auth()->user();
        $permissions = $user->getAllPermissions();
        foreach ($permissions as $permission) {
            $permissionId = $permission['id'];

            $pageName = str_replace('Resource', '', class_basename(__CLASS__));

            /** @var AdminCacheService $admCacheService */
            $admCacheService = app(AdminCacheService::class);
            $admPermissionFeatures = $admCacheService->getAdmPermissionFeature($permissionId);

            if (is_null($admPermissionFeatures) || $admPermissionFeatures->isEmpty()) {
                $admPermissionFeatures = AdmPermissionFeature::query()
                    ->where('permission_id', $permissionId)
                    ->pluck('feature_name');
                $admCacheService->putAdmPermissionFeature($permissionId, $admPermissionFeatures);
            }

            if ($admPermissionFeatures->contains($pageName)) {
                return  true;
            }
        }

        return false;
    }

    public static function canCreate(): bool
    {        
        if (self::isLeader()) {
            return true;
        }

        return false;
    }

    public static function canEdit(Model $record): bool
    {        
        if (self::isLeader()) {
            return true;
        }
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        if (self::isLeader()) {
            return true;
        }
        return false;
    }

    public static function canDeleteAny(): bool
    {
        if (self::isLeader()) {
            return true;
        }
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        if (self::isLeader()) {
            return true;
        }
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        if (self::isLeader()) {
            return true;
        }
        return false;
    }
}
