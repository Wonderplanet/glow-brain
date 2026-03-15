<?php

declare(strict_types=1);

namespace App\Domain\Game\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Services\LegalDocumentService;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Game\Services\AssetDataManifestService;
use App\Domain\Game\Services\VersionDataManifestService;
use App\Domain\User\Repositories\UsrUserRepository;
use App\Http\Responses\ResultData\GameVersionResultData;
use WonderPlanet\Domain\Common\Enums\Language;

class GameVersionUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrUserRepository $userService,
        private AssetDataManifestService $assetDataManifestService,
        private readonly VersionDataManifestService $versionDataManifestService,
        private LegalDocumentService $legalDocumentService,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string $language
     * @param int $platform
     * @param string|null $version
     * @return GameVersionResultData
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(
        CurrentUser $user,
        string $language,
        int $platform,
        ?string $version
    ): GameVersionResultData {
        $now = $this->clock->now();
        $usrUser = $this->userService->findById($user->id);

        // マスターデータ＆アセットデータバージョン取得
        $languageEnum = Language::from($language);
        $masterDataManifest = $this->versionDataManifestService
            ->getCurrentActiveMstManifest($languageEnum, $version, $now);
        $operationDataManifest = $this->versionDataManifestService
            ->getCurrentActiveOprManifest($languageEnum, $version, $now);
        $assetDataManifest = $this->assetDataManifestService->getCurrentActiveManifest($platform, $version, $now);

        $tosInfo = $this->legalDocumentService->getTosInfo($language);
        $privacyPolicyInfo = $this->legalDocumentService->getPrivacyPolicyInfo($language);
        $globalCnsntInfo = $this->legalDocumentService->getGlobalConsentInfo($language);
        $iaaInfo = $this->legalDocumentService->getIaaInfo($language);

        $this->processWithoutUserTransactionChanges();

        return new GameVersionResultData(
            $masterDataManifest['hash'],
            $masterDataManifest['path'],
            $masterDataManifest['i18nHash'],
            $masterDataManifest['i18nPath'],
            $operationDataManifest['hash'],
            $operationDataManifest['path'],
            $operationDataManifest['i18nHash'],
            $operationDataManifest['i18nPath'],
            $assetDataManifest['catalog_data_path'],
            $assetDataManifest['asset_hash'],
            $tosInfo['version'],
            $usrUser->getTosVersion(),
            $tosInfo['url'],
            $privacyPolicyInfo['version'],
            $usrUser->getPrivacyPolicyVersion(),
            $privacyPolicyInfo['url'],
            $globalCnsntInfo['version'],
            $usrUser->getGlobalConsentVersion(),
            $globalCnsntInfo['url'],
            $iaaInfo['version'],
            $usrUser->getIaaVersion(),
            $iaaInfo['url'],
        );
    }
}
