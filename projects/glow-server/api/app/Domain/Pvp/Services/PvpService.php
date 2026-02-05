<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Resource\Entities\ArtworkPartyStatus;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Resource\Mst\Entities\MstPvpEntity;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\Resource\Usr\Entities\UsrOutpostEnhancementEntity;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\PvpLoginData;
use App\Http\Responses\Data\PvpUnitData;
use App\Http\Responses\Data\UsrPvpInGameStatusData;
use App\Http\Responses\Data\UsrPvpStatusData;
use Carbon\CarbonImmutable;

class PvpService
{
    public function __construct(
        private Clock $clock,
        private MstPvpRepository $mstPvpRepository,
        private UsrPvpRepository $usrPvpRepository,
        private UsrPvpSessionRepository $usrPvpSessionRepository,
        private SysPvpSeasonRepository $sysPvpSeasonRepository,
    ) {
    }

    public function resetUsrPvp(
        UsrPvpInterface $usrPvp,
        MstPvpEntity $mstPvp,
        CarbonImmutable $now,
        bool $isSave = true,
    ): void {
        $latestResetAt = $usrPvp->getLatestResetAt();
        $isResettable = is_null($latestResetAt) || $this->clock->isFirstToday($latestResetAt);
        if (! $isResettable) {
            // リセットが必要でない場合は何もしない
            return;
        }

        $usrPvp->resetRemainingChallengeCounts(
            $mstPvp->getMaxDailyChallengeCount(),
            $mstPvp->getMaxDailyItemChallengeCount(),
            $now,
        );

        if ($isSave) {
            $this->usrPvpRepository->syncModel($usrPvp);
        }
    }

    /**
     * 現在時刻の西暦と週番号から現在のシーズンIDを生成します。
     *
     * @param CarbonImmutable $now
     * @return string
     */
    public function getCurrentSeasonId(CarbonImmutable $now): string
    {
        // 週番号の切り替わりを日本時間を基準にする
        $now = $this->clock->setLogicTimezone($now);

        // Assuming the current season ID is based on the year and week number
        $isoWeek = $now->isoWeek();
        $isoWeekYear = $now->isoWeekYear();
        // YYYY0WW format
        return sprintf(
            '%04d0%02d',
            $isoWeekYear,
            $isoWeek
        );
    }

    /**
     * ログイン時のレスポンスで必要なPVP情報を取得する
     *
     * PVPの情報はログインでは必須ではないため、DB更新は避ける
     */
    public function fetchPvpLoginData(
        string $usrUserId,
        CarbonImmutable $now,
    ): PvpLoginData {
        $sysPvpSeasonId = $this->getCurrentSeasonId($now);
        $sysPvpSeasonEntity = $this->sysPvpSeasonRepository->getCurrentOrMake(
            $sysPvpSeasonId,
            $now
        );

        $mstPvp = $this->mstPvpRepository->getDefaultOrTargetById($sysPvpSeasonEntity->getId(), false);
        $usrPvp = $this->usrPvpRepository->getBySysPvpSeasonId(
            $usrUserId,
            $sysPvpSeasonId,
        );

        $usrPvpStatus = $this->makeUsrPvpStatusData($usrPvp, $mstPvp, $now, false);

        return new PvpLoginData(
            $sysPvpSeasonEntity,
            $usrPvpStatus,
        );
    }

    public function makeUsrPvpStatusData(
        ?UsrPvpInterface $usrPvp,
        ?MstPvpEntity $mstPvp,
        CarbonImmutable $now,
        bool $isSave
    ): UsrPvpStatusData {
        if ($usrPvp === null) {
            // 未開催もしくは未参加の場合のデフォルト値
            return new UsrPvpStatusData(
                0,
                0,
                PvpRankClassType::BRONZE,
                0,
                $mstPvp?->getMaxDailyChallengeCount() ?? 0,
                $mstPvp?->getMaxDailyItemChallengeCount() ?? 0,
            );
        }

        if (is_null($mstPvp)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                'MstPvp not found for the current season.'
            );
        }
        $this->resetUsrPvp($usrPvp, $mstPvp, $now, $isSave);

        return new UsrPvpStatusData(
            $usrPvp->getScore(),
            $usrPvp->getMaxReceivedScoreReward(),
            $usrPvp->getPvpRankClassTypeEnum(),
            $usrPvp->getPvpRankClassLevel(),
            $usrPvp->getDailyRemainingChallengeCount(),
            $usrPvp->getDailyRemainingItemChallengeCount()
        );
    }

    /**
     * 現在のシーズンIDを取得し、存在しない場合はエラーを投げます。
     * ただし、集計期間のデータがある場合はそのデータを返します。
     *
     * @param CarbonImmutable $now
     * @return ?SysPvpSeasonEntity
     */
    public function getCurrentSysPvpSeason(CarbonImmutable $now, bool $isThrowError = true): ?SysPvpSeasonEntity
    {
        $currentSeasonId = $this->getCurrentSeasonId($now);
        return $this->sysPvpSeasonRepository->getCurrent($currentSeasonId, $now, $isThrowError);
    }

    /**
     * 指定されたシーズンIDの前のシーズンを取得します。
     *
     * @return ?SysPvpSeasonEntity
     */
    public function getPreviousSysPvpSeason(CarbonImmutable $now, bool $isThrowError = true): ?SysPvpSeasonEntity
    {
        $sysPvpSeasonId = $this->getCurrentSeasonId($now);
        return $this->sysPvpSeasonRepository->getPrevious($sysPvpSeasonId, $isThrowError);
    }

    /**
     * @param array<mixed> $jsonData
     */
    public function convertJsonToOpponentPvpStatus(
        array $jsonData,
    ): OpponentPvpStatusData {
        $pvpUserProfile = isset($jsonData['pvpUserProfile'])
            ? (array) $jsonData['pvpUserProfile'] : [];
        $unitStatuses = isset($jsonData['unitStatuses'])
            ? (array) $jsonData['unitStatuses'] : [];
        $usrOutpostEnhancements = isset($jsonData['usrOutpostEnhancements'])
            ? (array) $jsonData['usrOutpostEnhancements'] : [];
        $usrEncyclopediaEffects = isset($jsonData['usrEncyclopediaEffects'])
            ? (array) $jsonData['usrEncyclopediaEffects'] : [];
        $mstArtworkIds = isset($jsonData['mstArtworkIds'])
            ? (array) $jsonData['mstArtworkIds'] : [];

        $pvpUnits = collect($unitStatuses)->map(function ($unit) {
            return new PvpUnitData(
                $unit['mstUnitId'] ?? '',
                $unit['level'] ?? 0,
                $unit['rank'] ?? 0,
                $unit['gradeLevel'] ?? 0,
            );
        });

        $opponentSelectStatusData = new OpponentSelectStatusData(
            $pvpUserProfile['myId'] ?? '',
            $pvpUserProfile['name'] ?? '',
            $pvpUserProfile['mstUnitId'] ?? '',
            $pvpUserProfile['mstEmblemId'] ?? '',
            $pvpUserProfile['score'] ?? 0,
            $pvpUnits,
            $pvpUserProfile['winAddPoint'] ?? 0,
            PvpMatchingType::tryFrom($pvpUserProfile['matchingType'] ?? '') ?? PvpMatchingType::None,
        );

        $usrOutpostEnhancements = collect($usrOutpostEnhancements)->map(function ($enhancement) {
            return new UsrOutpostEnhancementEntity(
                $enhancement['mst_outpost_id'] ?? '',
                $enhancement['mst_outpost_enhancement_id'] ?? '',
                $enhancement['level'] ?? 0,
            );
        });
        $usrEncyclopediaEffects = collect($usrEncyclopediaEffects)->map(function ($effect) {
            return new PvpEncyclopediaEffect(
                $effect['mstEncyclopediaEffectId'] ?? '',
            );
        });

        // OpponentPvpStatusData用のartworkPartyStatusesを復元
        $opponentArtworkPartyStatuses = collect();
        if (isset($jsonData['artworkPartyStatuses'])) {
            $opponentArtworkPartyStatuses = collect($jsonData['artworkPartyStatuses'])->map(function ($status) {
                return new ArtworkPartyStatus(
                    $status['mstArtworkId'] ?? '',
                    $status['gradeLevel'] ?? 0,
                );
            });
        }

        return new OpponentPvpStatusData(
            $opponentSelectStatusData,
            $pvpUnits,
            $usrOutpostEnhancements,
            $usrEncyclopediaEffects,
            collect($mstArtworkIds),
            $opponentArtworkPartyStatuses
        );
    }

    public function makeUsrPvpInGameStatusData(
        string $usrUserId,
    ): UsrPvpInGameStatusData {
        $usrPvpSession = $this->usrPvpSessionRepository->findByUsrUserId($usrUserId);
        return new UsrPvpInGameStatusData($usrPvpSession);
    }

    public function getLatestPlayedUsrPvp(string $usrUserId, string $currentSysPvpSeasonId): ?UsrPvpInterface
    {
        $sysPvpSeasons = $this->sysPvpSeasonRepository->getPreviousWithCount(
            $currentSysPvpSeasonId,
            PvpConstant::SEASON_CONSIDER_LIMIT
        );
        $sysPvpSeasonIds = $sysPvpSeasons->keys();
        $usrPvps = $this->usrPvpRepository->getBySysPvpSeasonIds($usrUserId, $sysPvpSeasonIds);
        return $usrPvps->sortByDesc(fn($usrPvp) => $usrPvp->getSysPvpSeasonId())->first();
    }
}
