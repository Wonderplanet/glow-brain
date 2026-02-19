<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Outpost\Delegators\OutpostDelegator;
use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Entities\PvpInGameBattleLog;
use App\Domain\Pvp\Entities\PvpResultPoints;
use App\Domain\Pvp\Enums\LogPvpResult;
use App\Domain\Pvp\Enums\PvpBonusType;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Enums\PvpRankClassType;
use App\Domain\Pvp\Models\UsrPvpInterface as UsrPvp;
use App\Domain\Pvp\Models\UsrPvpSessionInterface as UsrPvpSession;
use App\Domain\Pvp\Repositories\LogPvpActionRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Domain\Resource\Entities\LogTriggers\PvpChallengeLogTrigger;
use App\Domain\Resource\Entities\Unit;
use App\Domain\Resource\Mst\Entities\MstPvpEntity;
use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity;
use App\Domain\Resource\Mst\Repositories\MstPvpBonusPointRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRankRepository;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Domain\Resource\Usr\Entities\UsrArtworkEntity;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\PvpUnitData;
use Carbon\CarbonImmutable;

class PvpEndService
{
    public function __construct(
        private readonly UsrPvpRepository $usrPvpRepository,
        private readonly UsrPvpSessionRepository $usrPvpSessionRepository,
        private readonly MstPvpRankRepository $mstPvpRankRepository,
        private readonly MstPvpBonusPointRepository $mstPvpBonusPointRepository,
        private readonly LogPvpActionRepository $logPvpActionRepository,
        private readonly PvpCacheService $pvpCacheService,
        private readonly PvpRewardService $pvpRewardService,
        private readonly MstConfigService $mstConfigService,
        private readonly UserDelegator $userDelegator,
        private readonly EncyclopediaDelegator $encyclopediaDelegator,
        private readonly EncyclopediaEffectDelegator $encyclopediaEffectDelegator,
        private readonly OutpostDelegator $outpostDelegator,
        private readonly ItemDelegator $itemDelegator,
        private readonly PartyDelegator $partyDelegator,
        private readonly PvpMissionTriggerService $pvpMissionTriggerService,
        private readonly UnitDelegator $unitDelegator,
        private readonly RewardDelegator $rewardDelegator,
    ) {
    }

    public function validateCanEnd(
        UsrPvpSession $usrPvpSession,
        SysPvpSeasonEntity $sysPvpSeason,
        CarbonImmutable $now
    ): void {
        if (!$usrPvpSession->isStarted()) {
            throw new GameException(
                ErrorCode::PVP_SESSION_NOT_FOUND,
                'pvp session is not in game',
            );
        }

        if (! $sysPvpSeason->isInSeason($now)) {
            throw new GameException(
                ErrorCode::PVP_SEASON_PERIOD_OUTSIDE,
                'sys_pvp_season is not in season. sys_pvp_season_id: ' . $sysPvpSeason->getId()
            );
        }
    }

    public function end(
        MstPvpEntity $mstPvp,
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession,
        PvpInGameBattleLog $inGameBattleLog,
        bool $isWin,
        PvpMatchingType $pvpMatchingType,
        CarbonImmutable $now
    ): PvpResultPoints {
        $this->consumeChallengeAttempt(
            $usrPvp,
            $usrPvpSession,
            $mstPvp->getItemChallengeCostAmount(),
            $usrPvpSession->getSysPvpSeasonId()
        );

        $this->endSession($usrPvp, $usrPvpSession, $now);

        $beforePvpRankClassType = $usrPvp->getPvpRankClassType();
        $beforePvpRankClassLevel = $usrPvp->getPvpRankClassLevel();
        $pvpResultPoints = $this->updateScore(
            $usrPvp,
            $usrPvpSession,
            $inGameBattleLog,
            $isWin,
            $mstPvp->getRankingMinPvpRankClass(),
            $pvpMatchingType
        );

        // 累計ポイント報酬の付与
        $this->addTotalScoreRewards($usrPvp);

        $myOpponentPvpStatus = $this->getOpponentPvpStatus($usrPvp, $usrPvpSession);
        $this->pvpCacheService->addOpponentStatus(
            $usrPvpSession->getSysPvpSeasonId(),
            $myOpponentPvpStatus->getPvpUserProfile()->getMyId(),
            $myOpponentPvpStatus
        );

        $this->updateOpponentCandidate(
            $myOpponentPvpStatus->getPvpUserProfile()->getMyId(),
            $usrPvp,
            $usrPvpSession,
            $beforePvpRankClassType,
            $beforePvpRankClassLevel
        );

        $this->createLog($usrPvp, $usrPvpSession, $inGameBattleLog, $myOpponentPvpStatus, $isWin);

        // 勝利時にミッショントリガーを送信
        if ($isWin) {
            $this->pvpMissionTriggerService->sendWinTriggers();
        }

        return $pvpResultPoints;
    }

    private function updateOpponentCandidate(
        string $myId,
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession,
        string $beforePvpRankClassType,
        int $beforePvpRankClassLevel
    ): void {
        if (
            $usrPvp->getPvpRankClassType() !== $beforePvpRankClassType
            || $usrPvp->getPvpRankClassLevel() !== $beforePvpRankClassLevel
        ) {
            // ランククラスが変わっている場合は、前のランククラスのキャッシュを削除
            $this->pvpCacheService->deleteOpponentCandidate(
                $usrPvpSession->getSysPvpSeasonId(),
                $myId,
                $beforePvpRankClassType,
                $beforePvpRankClassLevel,
            );
        }
        // 新しいランククラスのキャッシュに追加/更新
        $this->pvpCacheService->addOpponentCandidate(
            $usrPvpSession->getSysPvpSeasonId(),
            $myId,
            $usrPvp->getPvpRankClassType(),
            $usrPvp->getPvpRankClassLevel(),
            $usrPvp->getScore()
        );
    }

    private function createLog(
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession,
        PvpInGameBattleLog $inGameBattleLog,
        OpponentPvpStatusData $myOpponentPvpStatus,
        bool $isWin
    ): void {
        $result = $isWin ? LogPvpResult::VICTORY : LogPvpResult::DEFEAT;
        $this->logPvpActionRepository->create(
            $usrPvp->getUsrUserId(),
            $usrPvpSession->getSysPvpSeasonId(),
            $result,
            $myOpponentPvpStatus->formatToJson(),
            $usrPvpSession->getOpponentMyId(),
            $usrPvpSession->getOpponentPvpStatus(),
            $inGameBattleLog->formatToLog(),
        );
    }

    private function getOpponentPvpStatus(
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession
    ): OpponentPvpStatusData {
        $usrUserId = $usrPvp->getUsrUserId();

        // プロフィール
        $usrUserProfile = $this->userDelegator->getUsrUserProfileByUsrUserId($usrUserId);
        $party = $this->partyDelegator->getParty(
            usrUserId: $usrUserId,
            partyNo: $usrPvpSession->getPartyNo(),
        );

        // パーティキャラ
        $usrUnits = $party->getUnits();
        $unitStatuses = $usrUnits->map(function (Unit $unit) {
            $usrUnit = $unit->getUsrUnit();
            return new PvpUnitData(
                $usrUnit->getMstUnitId(),
                $usrUnit->getLevel(),
                $usrUnit->getRank(),
                $usrUnit->getGradeLevel(),
            );
        });

        $pvpUserProfile = new OpponentSelectStatusData(
            myId: $usrUserProfile->getMyId(),
            name: $usrUserProfile->getName(),
            mstUnitId: $usrUserProfile->getMstUnitId(),
            mstEmblemId: $usrUserProfile->getMstEmblemId(),
            score: $usrPvp->getScore(),
            partyPvpUnitDatas: $unitStatuses,
            winAddPoint: 0,
        );

        // ゲート
        $usrOutpostEnhancements = $this->outpostDelegator
            ->getUsrOutpostEnhancementsByUsedOutpost($usrUserId)
            ->map(fn($enhancement) => $enhancement->toEntity());

        // キャラ図鑑ランク効果
        $unitGradeLevelTotalCount = $this->unitDelegator->getGradeLevelTotalCount($usrUserId);
        $mstUnitEncyclopediaEffects = $this->encyclopediaEffectDelegator->getMstUnitEncyclopediaEffectsByGrade(
            $unitGradeLevelTotalCount,
        );
        $pvpEncyclopediaEffects = $mstUnitEncyclopediaEffects
            ->map(function (MstUnitEncyclopediaEffectEntity $mstUnitEncyclopediaEffect) {
                return new PvpEncyclopediaEffect(
                    mstEncyclopediaEffectId: $mstUnitEncyclopediaEffect->getId(),
                );
            });

        // 原画
        $usrArtworks = $this->encyclopediaDelegator->getUsrArtworks($usrUserId);
        $mstArtworkIds = $usrArtworks->map(fn(UsrArtworkEntity $artwork) => $artwork->getMstArtworkId())->values();

        return new OpponentPvpStatusData(
            pvpUserProfile: $pvpUserProfile,
            pvpUnits: $unitStatuses,
            usrOutpostEnhancements: $usrOutpostEnhancements,
            usrEncyclopediaEffects: $pvpEncyclopediaEffects,
            mstArtworkIds: $mstArtworkIds,
        );
    }

    public function updateScore(
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession,
        PvpInGameBattleLog $inGameBattleLog,
        bool $isWin,
        ?string $rankingMinPvpRankClassType,
        PvpMatchingType $pvpMatchingType
    ): PvpResultPoints {
        // 条件に応じてスコアを変動
        $resultPoint = $this->getResultPoint($usrPvp, $isWin);
        $clearTimeBonus = $isWin ? $this->getClearTimeBonus($inGameBattleLog->getClearTimeMs()) : 0;
        $opponentBonus = $isWin ? $this->getOpponentBonus($usrPvp->getPvpRankClassType(), $pvpMatchingType) : 0;
        $deltaPoint = $resultPoint + $clearTimeBonus + $opponentBonus;
        $usrPvp->adjustScore($deltaPoint);

        // スコアがマイナスにならないように調整
        if ($usrPvp->getScore() < 0) {
            $usrPvp->setScore(0);
        }

        // 加算後のスコアでランクタイプとレベルを更新
        $afterMstPvpRank = $this->mstPvpRankRepository->getByScore($usrPvp->getScore(), true);
        $usrPvp->updatePvpRankClass(
            $afterMstPvpRank->getRankClassType(),
            $afterMstPvpRank->getRankClassLevel(),
        );

        // ランキングに含むかどうかのチェック、含まない場合はここで終了
        if ($rankingMinPvpRankClassType !== null) {
            $rankingMinPvpRankClassType = PvpRankClassType::tryFrom($rankingMinPvpRankClassType);
            if (
                $rankingMinPvpRankClassType !== null
                && $afterMstPvpRank->getRankClassType()->order() < $rankingMinPvpRankClassType->order()
            ) {
                $this->usrPvpRepository->syncModel($usrPvp);
                return new PvpResultPoints(
                    $resultPoint,
                    $clearTimeBonus,
                    $opponentBonus,
                );
            }
        }
        // チート判定されている場合はランキング更新用のスコアを上書き
        // DBに保存するスコアについては受け取った値で更新する
        $updateScore = $usrPvp->isExcludedRanking()
            ? PvpConstant::RANKING_CHEATER_SCORE
            : $usrPvp->getScore();
        $this->pvpCacheService->addRankingScore(
            $usrPvpSession->getSysPvpSeasonId(),
            $usrPvp->getUsrUserId(),
            $updateScore,
        );

        $this->usrPvpRepository->syncModel($usrPvp);
        return new PvpResultPoints(
            $resultPoint,
            $clearTimeBonus,
            $opponentBonus,
        );
    }

    public function addTotalScoreRewards(UsrPvp $usrPvp): void
    {
        if ($usrPvp->getScore() > $usrPvp->getMaxReceivedScoreReward()) {
            $pvpTotalScoreRewards = $this->pvpRewardService->getTotalScoreRewards(
                $usrPvp->getSysPvpSeasonId(),
                $usrPvp->getScore(),
                $usrPvp->getMaxReceivedScoreReward(),
            );
            if ($pvpTotalScoreRewards->isNotEmpty()) {
                $this->rewardDelegator->addRewards($pvpTotalScoreRewards);
                $usrPvp->receiveScoreReward();
                $this->usrPvpRepository->syncModel($usrPvp);
            }
        }
    }

    public function abortSession(
        UsrPvpSession $usrPvpSession,
    ): void {
        $usrPvpSession->closeSession();
        $this->usrPvpSessionRepository->syncModel($usrPvpSession);
    }

    public function endSession(
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession,
        CarbonImmutable $now
    ): void {
        $usrPvp->setLastPlayedAt($now);
        $usrPvpSession->closeSession();
        $this->usrPvpRepository->syncModel($usrPvp);
        $this->usrPvpSessionRepository->syncModel($usrPvpSession);
    }

    /**
     * 残りの挑戦回数を消費し、必要に応じてアイテムを消費する
     */
    public function consumeChallengeAttempt(
        UsrPvp $usrPvp,
        UsrPvpSession $usrPvpSession,
        int $costAmount,
        string $sysPvpSeasonId
    ): void {
        // is_use_itemが1の場合はランクマッチチケット（アイテム）を優先的に消費
        if ($usrPvpSession->isUseItem()) {
            if ($this->tryConsumeItemChallenge($usrPvp, $costAmount, $sysPvpSeasonId)) {
                return;
            }

            // is_use_itemが1なのにアイテムが使えない場合は例外を投げる
            throw new GameException(
                ErrorCode::PVP_NO_CHALLENGE_RIGHT,
                'No item challenge rights left for PVP.',
            );
        }

        // is_use_itemが0の場合は従来通り無料回数を優先的に消費
        if ($usrPvp->tryDecrementDailyRemainingChallengeCount()) {
            $this->usrPvpRepository->syncModel($usrPvp);
            return;
        }

        throw new GameException(
            ErrorCode::PVP_NO_CHALLENGE_RIGHT,
            'No challenge rights left for PVP.',
        );
    }

    /**
     * アイテム挑戦回数の消費を試す
     */
    private function tryConsumeItemChallenge(UsrPvp $usrPvp, int $costAmount, string $sysPvpSeasonId): bool
    {
        $remainingItemChallengeCount = $usrPvp->getDailyRemainingItemChallengeCount();
        if ($remainingItemChallengeCount <= 0) {
            return false;
        }

        $mstItemId = $this->mstConfigService->getPvpChallengeItemId();
        if ($mstItemId === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                'mst_config for PVP challenge item is not found.',
            );
        }

        $this->itemDelegator->useItemByMstItemId(
            usrUserId: $usrPvp->getUsrUserId(),
            mstItemId: $mstItemId,
            useNum: $costAmount,
            logTrigger: new PvpChallengeLogTrigger($sysPvpSeasonId)
        );

        // アイテム消費をしたので、残りのアイテム挑戦回数を減らす
        if ($usrPvp->tryDecrementDailyRemainingItemChallengeCount()) {
            $this->usrPvpRepository->syncModel($usrPvp);
            return true;
        }

        return false;
    }

    /**
     * 現在ランクを元に勝敗によるポイントを取得する
     *
     * @param UsrPvp $usrPvp
     * @param boolean $isWin
     * @return integer
     */
    public function getResultPoint(
        UsrPvp $usrPvp,
        bool $isWin
    ): int {
        $mstPvpRank = $this->mstPvpRankRepository->getByClassTypeAndLevel(
            $usrPvp->getPvpRankClassType(),
            $usrPvp->getPvpRankClassLevel(),
            true, // 存在しない場合はエラーにする
        );
        if ($isWin) {
            return $mstPvpRank->getWinAddPoint();
        } else {
            return $mstPvpRank->getLoseSubPoint() * -1; // 負けた場合はマイナス値を返す
        }
    }

    /**
     * クリアタイムボーナスを取得する
     *
     * @param int $clearTimeMs クリアタイム
     * @return integer クリアタイムボーナス
     */
    public function getClearTimeBonus(
        int $clearTimeMs,
    ): int {
        $mstPvpBonusPoint = $this->mstPvpBonusPointRepository->getByClearTime($clearTimeMs);
        if ($mstPvpBonusPoint === null) {
            return 0; // ボーナスがない場合は0ポイント
        }
        return $mstPvpBonusPoint->getBonusPoint();
    }

    /**
     * 対戦相手のスコア差に応じたボーナスポイントを取得する
     *
     * @return int 対戦相手のスコアに応じたボーナスポイント
     */
    public function getOpponentBonus(
        string $rankClassType,
        PvpMatchingType $pvpMatchingType,
    ): int {

        $opponentBonuses = $this->mstPvpBonusPointRepository->getByOpponentScore($rankClassType);
        $winUpperBonus = $opponentBonuses->get(PvpBonusType::WinUpperBonus->value);
        $winSameBonus = $opponentBonuses->get(PvpBonusType::WinSameBonus->value);
        $winLowerBonus = $opponentBonuses->get(PvpBonusType::WinLowerBonus->value);

        return match ($pvpMatchingType) {
            PvpMatchingType::Upper => $winUpperBonus?->getBonusPoint() ?? 0,
            PvpMatchingType::Same => $winSameBonus?->getBonusPoint() ?? 0,
            PvpMatchingType::Lower => $winLowerBonus?->getBonusPoint() ?? 0,
            default => 0,
        };
    }
}
