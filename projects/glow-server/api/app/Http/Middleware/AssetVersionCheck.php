<?php

namespace App\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Game\Services\AssetDataManifestService;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;

class AssetVersionCheck
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws GameException
     */
    public function handle(Request $request, Closure $next)
    {
        $hash = $request->header(System::HEADER_ASSET_HASH);
        $clientVersion = $request->header(System::CLIENT_VERSION);
        $platform = $request->header(System::HEADER_PLATFORM);
        if (is_null($hash) || is_null($clientVersion) || is_null($platform)) {
            // ヘッダーが存在しない場合は、そのまま処理を継続（主に初回アクセス時を想定）
            return $next($request);
        }

        if ($this->isAssetSkip($request->path())) {
            // チェック不要なAPIパスの場合は、そのまま処理を継続
            return $next($request);
        }

        $now = app(Clock::class)->now();
        if ($this->isRequireUpdate((int)$platform, $clientVersion, $hash, $now)) {
            // アセットデータの更新が必要な場合は、クライアント側にその旨を通知する
            throw new GameException(ErrorCode::REQUIRE_RESOURCE_UPDATE);
        }

        return $next($request);
    }

    /**
     * アセットバージョン更新チェックをスキップするかの判定
     *
     * @param string $path
     *
     * @return bool
     */
    private function isAssetSkip(string $path): bool
    {
        return isset(System::ASSET_CHECK_THROUGH_API[$path]);
    }

    /**
     * アセットバージョン更新チェック
     *
     * @param int $platform
     * @param string $clientVersion
     * @param string $hash
     * @param CarbonImmutable $now
     *
     * @return bool
     */
    private function isRequireUpdate(int $platform, string $clientVersion, string $hash, CarbonImmutable $now): bool
    {
        /** @var AssetDataManifestService $assetDataManifestService */
        $assetDataManifestService = app()->make(AssetDataManifestService::class);
        $assetVersion = $assetDataManifestService->getCurrentActiveAsset($platform, $clientVersion, $now);
        if (is_null($assetVersion)) {
            // 現時点で有効なReleaseControlが存在しない場合は、そのまま処理を継続
            return false;
        }

        return $assetVersion->isRequireUpdate($hash);
    }
}
