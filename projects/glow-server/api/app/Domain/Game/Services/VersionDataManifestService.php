<?php

declare(strict_types=1);

namespace App\Domain\Game\Services;

use App\Domain\Resource\Mng\Services\MngMasterReleaseService;
use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Common\Enums\Language;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Utils\MasterDataUtility;

/**
 * クライアントに渡すゲームバージョンを取得するためのサービス
 */
class VersionDataManifestService
{
    private MngMasterReleaseVersionEntity|null $mngMasterReleaseVersionEntity = null;

    public function __construct(
        private readonly MngMasterReleaseService $mngMasterReleaseService,
    ) {
    }

    /**
     * クライアントバージョンを元にマスターリリースバージョン情報を取得
     *
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return MngMasterReleaseVersionEntity
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getMngMasterReleaseVersionEntity(
        string $clientVersion,
        CarbonImmutable $now
    ): MngMasterReleaseVersionEntity {
        if (!is_null($this->mngMasterReleaseVersionEntity)) {
            return $this->mngMasterReleaseVersionEntity;
        }

        $this->mngMasterReleaseVersionEntity =
            $this->mngMasterReleaseService->getMasterReleaseVersionByClientVersion($clientVersion, $now);

        return $this->mngMasterReleaseVersionEntity;
    }

    /**
     * マスターデータのハッシュとパスを取得
     *
     * @param Language $language
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return array<string, string>
     */
    public function getCurrentActiveMstManifest(Language $language, string $clientVersion, CarbonImmutable $now): array
    {
        /** @var MngMasterReleaseVersionEntity $versionEntity */
        $versionEntity = $this->getMngMasterReleaseVersionEntity($clientVersion, $now);

        $releaseKey = $versionEntity->getReleaseKey();
        $hash = $versionEntity->getClientMstDataHash();
        $path = MasterDataUtility::getPath(
            MasterData::MASTERDATA,
            $hash
        );

        $i18nHash = $versionEntity->getClientMstDataI18nHashByLanguage($language);
        $i18nPath = MasterDataUtility::getI18nPath(
            MasterData::MASTERDATA_I18N_PATH,
            MasterData::MASTERDATA_I18N,
            $language,
            $i18nHash
        );

        return [
            'hash'     => $hash,
            'path'     => $releaseKey . '/' . $path,
            'i18nHash' => $i18nHash,
            'i18nPath' => $releaseKey . '/' . $i18nPath,
        ];
    }

    /**
     * オペレーションデータのハッシュとパスを取得
     *
     * @param Language $language
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return array<string, string>
     */
    public function getCurrentActiveOprManifest(Language $language, string $clientVersion, CarbonImmutable $now): array
    {
        /** @var MngMasterReleaseVersionEntity $versionEntity */
        $versionEntity = $this->getMngMasterReleaseVersionEntity($clientVersion, $now);

        $releaseKey = $versionEntity->getReleaseKey();
        $hash = $this->mngMasterReleaseVersionEntity->getClientOprDataHash();
        $path = MasterDataUtility::getPath(
            MasterData::OPERATIONDATA,
            $hash
        );

        $i18nHash = $versionEntity->getClientOprDataI18nHashByLanguage($language);
        $i8nPath = MasterDataUtility::getI18nPath(
            MasterData::OPERATIONDATA_I18N_PATH,
            MasterData::OPERATIONDATA_I18N,
            $language,
            $i18nHash
        );

        return [
            'hash' => $hash,
            'path' => $releaseKey . '/' . $path,
            'i18nHash' => $i18nHash,
            'i18nPath' => $releaseKey . '/' . $i8nPath,
        ];
    }
}
