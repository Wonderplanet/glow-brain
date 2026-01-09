<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;

class AdminInitializeMstDatabaseConnection
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->isAllowedPath($request->path())) {
            // 許可したリクエストURLなら何もせず通す
            return $next($request);
        }

        /** @var MngMasterReleaseService $mngMasterReleaseService */
        $mngMasterReleaseService = app()->make(MngMasterReleaseService::class);

        /** @var MngMasterRelease|null $mngMasterRelease */
        $mngMasterRelease = $mngMasterReleaseService->getLatestReleasedMngMasterRelease();

        if (is_null($mngMasterRelease)) {
            // 配信中のマスターデータがなかった場合はエラーとする
            throw new \Exception('not found released master data');
        }

        return $next($request);
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isAllowedPath(string $path): bool
    {
        $isAllowedPath = false;

        // 特定文字列のURLなら許可する
        // ログイン後のダッシュボード画面、livewireのwebhook
        if (in_array($path, ['admin', 'livewire/update'], true)) {
            $isAllowedPath = true;
            return $isAllowedPath;
        }

        // エラーにさせたくないURL
        $allowedPaths = [
            'login',
            'logout',
            'mng-asset-releases',
            'mng-asset-release-imports',
            'mng-master-releases',
            'mng-master-release-versions',
            'mng-master-and-asset-release',
            'mng-master-release-imports',
        ];
        foreach ($allowedPaths as $allowedPath) {
            if (!preg_match("/{$allowedPath}/", $path)) {
                continue;
            }
            // 対象URLを含んだパスなら通す
            $isAllowedPath = true;
            break;
        }

        return $isAllowedPath;
    }
}
