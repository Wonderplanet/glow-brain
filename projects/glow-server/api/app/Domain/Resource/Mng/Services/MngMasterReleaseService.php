<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException;
use WonderPlanet\Domain\MasterAssetRelease\Facades\MasterReleaseVersion;

class MngMasterReleaseService
{
    /**
     * クライアントバージョンから互換性のあるマスターリリース情報のエンティティを取得する
     *
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return MngMasterReleaseVersionEntity
     * @throws GameException
     */
    public function getMasterReleaseVersionByClientVersion(
        string $clientVersion,
        CarbonImmutable $now,
    ): MngMasterReleaseVersionEntity {
        try {
            // 現在配信中のリリース情報を取得
            /** @var MngMasterReleaseVersionEntity $versionEntity */
            $versionEntity = MasterReleaseVersion::getApplyMasterReleaseVersionEntityByClientVersion(
                $clientVersion,
                $now,
            );

            // phpstanでExceptionが投げられないというエラーが出てしまうため、無視するように設定
            // @phpstan-ignore-next-line
        } catch (WpMasterReleaseIncompatibleClientVersionException $incompatibleException) {
            throw new GameException(
                ErrorCode::INCOMPATIBLE_MASTER_DATA_FROM_CLIENT_VERSION,
                $incompatibleException->getMessage()
            );

            // phpstanでExceptionが投げられないというエラーが出てしまうため、無視するように設定
            // @phpstan-ignore-next-line
        } catch (WpMasterReleaseApplyNotFoundException $applyNotFoundException) {
            throw new GameException(
                ErrorCode::NOT_FOUND_APPLY_MASTER_RELEASE,
                $applyNotFoundException->getMessage()
            );
        } catch (\Exception $e) {
            throw new GameException(ErrorCode::UNKNOWN_ERROR, $e->getMessage());
        }

        return $versionEntity;
    }

    /**
     * 配信中のリリースバージョン情報のコレクションを取得する
     * コレクションの内容はentityとclient_compatibility_versionを持つ
     *
     * @param CarbonImmutable $now
     * @return Collection
     * @throws GameException
     */
    public function getApplyMasterReleaseVersionEntities(CarbonImmutable $now): Collection
    {
        try {
            /**
             * 参考 コレクションの中身
             * $entityCollection = [
             *     [
             *         'entity' => MngMasterReleaseVersionEntity,
             *         'client_compatibility_version' => '1.0.0'
             *     ],
             * ];
             */
            $entityCollection
                = MasterReleaseVersion::getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection($now);

            // phpstanでExceptionが投げられないというエラーが出てしまうため、無視するように設定
            // @phpstan-ignore-next-line
        } catch (WpMasterReleaseApplyNotFoundException $applyNotFoundException) {
            throw new GameException(
                ErrorCode::NOT_FOUND_APPLY_MASTER_RELEASE,
                $applyNotFoundException->getMessage()
            );
        }

        return $entityCollection;
    }

    /**
     * 現在接続しているデータベース名からリリースバージョン情報を取得する
     *
     * @param CarbonImmutable $now
     * @return MngMasterReleaseVersionEntity
     * @throws GameException
     */
    public function getMngMasterReleaseVersionEntityByConfigDatabase(
        CarbonImmutable $now
    ): MngMasterReleaseVersionEntity {
        /** @var Collection $entityCollection */
        $entityCollection = $this->getApplyMasterReleaseVersionEntities($now);

        $versionMap = $entityCollection
            ->filter(function (array $map) {
                /** @var \WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity $entity */
                $entity = $map['entity'];
                return $entity->getDbName() === config('database.connections.mst.database');
            })
            ->first();

        if (is_null($versionMap)) {
            throw new \Exception(
                'Unmatched mst database name config: ' . config('database.connections.mst.database')
            );
        }

        return $versionMap['entity'];
    }
}
