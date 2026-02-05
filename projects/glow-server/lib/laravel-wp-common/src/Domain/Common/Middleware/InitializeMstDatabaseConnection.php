<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\MasterAssetRelease\Delegators\MasterReleaseDelegator;

/**
 * リリースバージョンに合わせてマスターDBへの接続を行うミドルウェア
 */
class InitializeMstDatabaseConnection
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var MasterReleaseDelegator $masterReleaseDelegator */
        $masterReleaseDelegator = app()->make(MasterReleaseDelegator::class);
        $now = CarbonImmutable::now();

        $clientVersion = $request->header(config('wp_master_asset_release.header_client_version'));

        $useLatestMasterData = $clientVersion === null && $this->shouldUseLatestMasterData($request);
        if ($useLatestMasterData) {
            $mngMasterReleaseVersionEntity = $masterReleaseDelegator
                ->getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection($now)
                ->first();
            $mngMasterReleaseVersionEntity = $mngMasterReleaseVersionEntity['entity'] ?? null;
        } else {
            // $clientVersionがnullの場合は空文字列として扱う
            $mngMasterReleaseVersionEntity = $masterReleaseDelegator
                ->getApplyMasterReleaseVersionEntityByClientVersion($clientVersion ?? '', $now);
        }

        $dbName = $mngMasterReleaseVersionEntity->getDbName();
        $this->setMstDatabaseConnectionDbName($dbName);

        Log::debug('マスターDBの向き先を設定しました', [
            'db_name' => $dbName,
        ]);

        return $next($request);
    }

    /**
     * @param string $dbName
     * @return void
     */
    private function setMstDatabaseConnectionDbName(string $dbName): void
    {
        // TODO: かなり強引なので、より安全な方法を検討する
        Config::set("database.connections.mst.database", $dbName);
    }

    /**
     * 最新マスタデータを使用すべきかを判定
     *
     * Webhookではクライアントバージョンが取得できないので
     * 現在時刻時点で有効な最新のマスターリリースを適用するかどうかを判定する
     *
     * @param Request $request
     * @return bool
     */
    private function shouldUseLatestMasterData(Request $request): bool
    {
        $useLatestMasterDataPaths = config('wp_common.use_latest_master_data_paths', []);

        foreach ($useLatestMasterDataPaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
