<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaEffectDelegator;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Entities\FormatMstPvpRankListResponse;
use App\Domain\Pvp\Entities\PvpEncyclopediaEffect;
use App\Domain\Pvp\Entities\PvpMatchingScoreRangeFormat;
use App\Domain\Pvp\Enums\PvpMatchingType;
use App\Domain\Pvp\Repositories\MstDummyOutpostRepository;
use App\Domain\Pvp\Repositories\MstDummyUserArtworkRepository;
use App\Domain\Pvp\Repositories\MstDummyUserRepository;
use App\Domain\Pvp\Repositories\MstDummyUserUnitRepository;
use App\Domain\Pvp\Repositories\MstPvpDummyRepository;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Pvp\Services\PvpCacheService;
use App\Domain\Resource\Mst\Entities\MstPvpDummyEntity;
use App\Domain\Resource\Mst\Entities\MstPvpMatchingScoreRangeEntity;
use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity;
use App\Domain\Resource\Mst\Repositories\MstOutpostEnhancementRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpMatchingScoreRangeRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRankRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Domain\Resource\Usr\Entities\UsrOutpostEnhancementEntity;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\OpponentPvpStatusData;
use App\Http\Responses\Data\OpponentSelectStatusData;
use App\Http\Responses\Data\PvpUnitData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PvpMatchingService
{
    public function __construct(
        public readonly UsrPvpRepository $usrPvpRepository,
        private MstPvpRankRepository $mstPvpRankRepository,
        private MstPvpDummyRepository $mstPvpDummyRepository,
        private MstDummyOutpostRepository $mstDummyOutpostRepository,
        private MstDummyUserUnitRepository $mstDummyUserUnitRepository,
        private MstDummyUserArtworkRepository $mstDummyUserArtworkRepository,
        private MstDummyUserRepository $mstDummyUserRepository,
        private MstPvpMatchingScoreRangeRepository $mstPvpMatchingScoreRangeRepository,
        private MstOutpostEnhancementRepository $mstOutpostEnhancementRepository,
        public readonly PvpCacheService $pvpCacheService,
        private readonly EncyclopediaEffectDelegator $encyclopediaEffectDelegator,
        public readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        public readonly MstPvpRepository $mstPvpRepository,
        private readonly PvpEndService $pvpEndService,
        private readonly UserDelegator $userDelegator,
    ) {
    }


    /**
     * 対戦相手の選出ステータスデータを取得する
     * @param string $usrUserId ユーザID
     * @param string $sysPvpSeasonId シーズンID
     * @return Collection<OpponentPvpStatusData> 対戦相手選出ステータスのコレクション
     */
    public function getMatchingOpponentSelectStatusDatas(
        string $usrUserId,
        string $sysPvpSeasonId,
        CarbonImmutable $now
    ): Collection {
        $sysPvpSeason = $this->sysPvpSeasonRepository->getCurrentOrCreate(
            $sysPvpSeasonId,
            $now
        );

        // ユーザのPVP情報を取得
        $mstPvp = $this->mstPvpRepository->getDefaultOrTargetById($sysPvpSeasonId);
        $usrPvp = $this->usrPvpRepository->getOrMake(
            $usrUserId,
            $sysPvpSeason->getId(),
            $mstPvp->getMaxDailyChallengeCount(),
            $mstPvp->getMaxDailyItemChallengeCount(),
            $now
        );
        // 抽出するRANGEのマスタデータを取得する
        $mstPvpMatchingScoreRange = $this->mstPvpMatchingScoreRangeRepository->getByTypeAndLevel(
            $usrPvp->getPvpRankClassType(),
            $usrPvp->getPvpRankClassLevel()
        );

        // PVPランクのマスターデータを取得(RequiredLowerScoreでソート)
        $mstPvpRanks = $this->mstPvpRankRepository->getAllSortedByRequiredLowerScore();

        $formatMstPvpRankList = $this->formatMstPvpRankList(
            $mstPvpRanks,
            $mstPvpMatchingScoreRange,
            $usrPvp->getScore()
        );

        $formatedMstPvpRanks = $formatMstPvpRankList->getFormatRanks();
        $targetRankKeyList = $formatMstPvpRankList->getTargetRankKeys();

        $responseUserDatas = [];
        $targetRankKey = [];

        $mstPvpDummies = $this->mstPvpDummyRepository->getDummyUsersByRankTypeAndLevel(
            $usrPvp->getPvpRankClassType(),
            $usrPvp->getPvpRankClassLevel(),
        );
        $mstPvpDummies = $mstPvpDummies->groupBy(function (MstPvpDummyEntity $entity) {
            return $entity->getMatchingType();
        });

        $usrUserProfile = $this->userDelegator->getUsrUserProfileByUsrUserId($usrUserId);
        $invalidMyIds = [$usrUserProfile->getMyId()];
        foreach ($targetRankKeyList as $index => $targetRankKeys) {
            $myId = null;
            $winAddPoint = 0;
            $pvpMatchingType = PvpMatchingType::from($index);

            // 取得するポイントの幅を取得
            $min = $usrPvp->getScore() + $mstPvpMatchingScoreRange->getMinScoreByType($pvpMatchingType);
            $max = $usrPvp->getScore() + $mstPvpMatchingScoreRange->getMaxScoreByType($pvpMatchingType);
            foreach ($targetRankKeys as $targetRankKey) {
                // キャッシュキーを作って該当のキャッシュからUserIdのリストを取得
                $myIds = $this->pvpCacheService->getOpponentCandidateRangeList(
                    $sysPvpSeasonId,
                    $targetRankKey['classType'],
                    $targetRankKey['classLevel'],
                    $min,
                    $max
                );
                $winAddPoint = $targetRankKey['winAddPoint'];
                $validMyIds = array_diff($myIds, $invalidMyIds);
                // キャッシュから帰ってきたUserIdのリストから抽選する
                $myId = $this->lotteryMyId($validMyIds);
                if (!is_null($myId)) {
                    break;
                }
            }

            $pvpUserData = null;
            if (!is_null($myId)) {
                // キャッシュからIDが取れた場合はそのまま使う
                $pvpUserData = $this->pvpCacheService->getOpponentStatus($sysPvpSeasonId, $myId);
                $invalidMyIds[] = $myId;
            }
            if (is_null($pvpUserData)) {
                // キャッシュが取れない場合はダミーデータを取る
                $myId = $mstPvpDummies->get($index)->random()->getMstDummyUserId();
                $maxScore = match ($pvpMatchingType) {
                    PvpMatchingType::Upper => $mstPvpMatchingScoreRange->getUpperRankMaxScore(),
                    PvpMatchingType::Same => $mstPvpMatchingScoreRange->getSameRankMaxScore(),
                    PvpMatchingType::Lower => $mstPvpMatchingScoreRange->getLowerRankMaxScore(),
                    default => 0,
                };
                $minScore = match ($pvpMatchingType) {
                    PvpMatchingType::Upper => $mstPvpMatchingScoreRange->getUpperRankMinScore(),
                    PvpMatchingType::Same => $mstPvpMatchingScoreRange->getSameRankMinScore(),
                    PvpMatchingType::Lower => $mstPvpMatchingScoreRange->getLowerRankMinScore(),
                    default => 0,
                };
                $pvpUserData = $this->getDummyOpponentPvpStatus(
                    $myId,
                    $winAddPoint,
                    $usrPvp->getScore(),
                    $maxScore,
                    $minScore,
                );
                $pvpMatchingType = PvpMatchingType::Lower; // ダミーデータは格下として扱う
            }
            $winAddPoint = $this->pvpEndService->getResultPoint($usrPvp, true);
            $winAddPoint += $this->pvpEndService->getOpponentBonus(
                $usrPvp->getPvpRankClassType(),
                $pvpMatchingType
            );
            $pvpUserData->setWinAddPoint($winAddPoint);
            $pvpUserData->setMatchingType($pvpMatchingType);
            $responseUserDatas[] = $pvpUserData;
        }

        return collect($responseUserDatas);
    }

    /**
     * ダミーデータを元にOpponentPvpStatusを作成する
     * @param string $myId ダミーユーザID
     * @param int $winAddPoint 勝利時に追加されるポイント
     * @return OpponentPvpStatusData
     */
    private function getDummyOpponentPvpStatus(
        string $myId,
        int $winAddPoint,
        int $myScore,
        int $maxRange,
        int $minRange,
    ): OpponentPvpStatusData {
        $mstDummyUser = $this->mstDummyUserRepository->getDummyUserById($myId);
        $mstPvpDummy = $this->mstPvpDummyRepository->getMstPvpDummyByDummyUserId($myId);

        $dummyUserId = $mstDummyUser->getId();
        $mstDummyUserName = $this->mstDummyUserRepository->getNameI18nByDummyUserId($dummyUserId);
        $mstDummyUserUnits = $this->mstDummyUserUnitRepository->getDummyUnitByUserId($dummyUserId);
        $mstDummyOutpostEnhancements
            = $this->mstDummyOutpostRepository->getMstDummyOutpostEnhancementIdByUserId($dummyUserId);
        $mstDummyOutpostEnhancementIds = $mstDummyOutpostEnhancements->map(function ($mstDummyOutpost) {
            return $mstDummyOutpost->getMstOutpostEnhancementId();
        });

        $mstOutpostEnhancements = $this->mstOutpostEnhancementRepository->getByIds($mstDummyOutpostEnhancementIds);

        // mstDummyOutpostsEnhancementsのmstOutpostIdにmstOutpostEnhancementsのmstOutpostIdを紐づける
        $usrOutpostEnhancements = collect();
        foreach ($mstDummyOutpostEnhancements as $mstDummyOutpost) {
            $mstOutpostEnhancement =
                $mstOutpostEnhancements->get(
                    $mstDummyOutpost->getMstOutpostEnhancementId()
                );

            // MstOutpostEnhancementが見つからない場合はスキップ
            if ($mstOutpostEnhancement === null) {
                continue;
            }

            $usrOutpostEnhancements->push(
                new UsrOutpostEnhancementEntity(
                    $mstOutpostEnhancement->getMstOutpostId(),
                    $mstOutpostEnhancement->getId(),
                    $mstDummyOutpost->getLevel()
                )
            );
        }

        $score = $this->getDummyScore($myScore, $maxRange, $minRange);

        $unitStatuses = $mstDummyUserUnits->map(function ($unit) {
            return new PvpUnitData(
                $unit->getMstUnitId(),
                $unit->getLevel(),
                $unit->getRank(),
                $unit->getGradeLevel(),
            );
        });

        $pvpUserProfile = new OpponentSelectStatusData(
            myId: $dummyUserId,
            name: $mstDummyUserName,
            mstUnitId: $mstDummyUser->getMstUnitId(),
            mstEmblemId: $mstDummyUser->getMstEmblemId(),
            score: $score,
            partyPvpUnitDatas: $unitStatuses,
            winAddPoint: $winAddPoint,
        );

        $mstUnitEncyclopediaEffects =
            $this->encyclopediaEffectDelegator->getMstUnitEncyclopediaEffectsByGrade(
                $mstDummyUser->getGradeUnitLevelTotalCount()
            );
        $pvpEncyclopediaEffects = $this->makePvpEncyclopediaEffects($mstUnitEncyclopediaEffects);
        $mstArtworkIds = $this->mstDummyUserArtworkRepository->getArtworkIdsByDummyUserId($dummyUserId);

        return new OpponentPvpStatusData(
            pvpUserProfile: $pvpUserProfile,
            pvpUnits: $unitStatuses,
            usrOutpostEnhancements: $usrOutpostEnhancements,
            usrEncyclopediaEffects: $pvpEncyclopediaEffects,
            mstArtworkIds: $mstArtworkIds,
        );
    }

    private function getDummyScore(int $myScore, int $maxRange, int $minRange): int
    {
        // ダミーユーザのスコアをランダムに生成する
        $score = $myScore + rand($minRange, $maxRange);
        return max(0, $score);
    }

    /**
     *  @param Collection<MstUnitEncyclopediaEffectEntity> $mstUnitEncyclopediaEffects
     *  @return Collection<PvpEncyclopediaEffect>
     */
    private function makePvpEncyclopediaEffects(Collection $mstUnitEncyclopediaEffects): Collection
    {
        $pvpEncyclopediaEffects = collect();
        foreach ($mstUnitEncyclopediaEffects as $mstUnitEncyclopediaEffect) {
            $pvpEncyclopediaEffects->push(
                new PvpEncyclopediaEffect($mstUnitEncyclopediaEffect->getId())
            );
        }
        return $pvpEncyclopediaEffects;
    }

    /**
     * PVPランクのマスターデータをフォーマットし、現在のランクとその上下のランクを取得する
     * @param Collection $mstPvpRanks PVPランクのマスターデータ
     * @param MstPvpMatchingScoreRangeEntity $mstPvpMatchingScoreRange
     * @param int $score ユーザーのスコア
     * @return FormatMstPvpRankListResponse フォーマットされたPVPランクのマスターデータ
     */
    private function formatMstPvpRankList(
        Collection $mstPvpRanks,
        MstPvpMatchingScoreRangeEntity $mstPvpMatchingScoreRange,
        int $score = 0
    ): FormatMstPvpRankListResponse {
        $formatedRanks = [];
        // Coolectionを配列に変換して、インデックスを振る
        $mstPvpRanks = array_values($mstPvpRanks->toArray());
        // PVPランクのマスターデータをフォーマット
        foreach ($mstPvpRanks as $key => $mstPvpRank) {
            if (isset($mstPvpRanks[$key + 1])) {
                $endScoreRange = $mstPvpRanks[$key + 1]->getRequiredLowerScore() - 1;
                // 2回目以降は前のランクのendScoreRangeをstartScoreRangeに設定
                $formatedRanks[$key]['endScoreRange'] = $mstPvpRanks[$key + 1]->getRequiredLowerScore() - 1;
            } else {
                $endScoreRange = PvpConstant::MAX_BATTLE_SCORE;
            }
            $pvpMatchingScoreRangeFormat = new PvpMatchingScoreRangeFormat(
                $mstPvpRank->getId(),
                $mstPvpRank->getRankClassType()->value,
                $mstPvpRank->getRankClassLevel(),
                $mstPvpRank->getRequiredLowerScore(),
                $endScoreRange,
                $mstPvpRank->getWinAddPoint(),
                $mstPvpRank->getLoseSubPoint()
            );

            $formatedRanks[$key] = $pvpMatchingScoreRangeFormat->formatToResponse();
        }
        $rankListByType = [
            PvpMatchingType::Upper,
            PvpMatchingType::Same,
            PvpMatchingType::Lower,
        ];
        foreach ($rankListByType as $rankType) {
            $targetRankKeys[$rankType->value] =
                $this->getRankRange($rankType, $formatedRanks, $mstPvpMatchingScoreRange, $score);
        }

        return new FormatMstPvpRankListResponse($formatedRanks, $targetRankKeys);
    }

    /**
     * 指定されたランクタイプに基づいて、スコア範囲の間にあるランクを取得する
     * @param array<mixed> $formatedRanks フォーマットされたPVPランクのマスターデータ
     * @param MstPvpMatchingScoreRangeEntity $mstPvpMatchingScoreRange
     * @param int $score ユーザーのスコア
     * @return array<mixed> マッチするランク範囲のリスト
     */
    private function getRankRange(
        PvpMatchingType $rankType,
        array $formatedRanks,
        MstPvpMatchingScoreRangeEntity $mstPvpMatchingScoreRange,
        int $score
    ): array {
        $upperRangeScore = 0;
        $lowerRangeScore = 0;
        if ($rankType === PvpMatchingType::Upper) {
            $upperRangeScore = $score + $mstPvpMatchingScoreRange->getUpperRankMaxScore();
            $lowerRangeScore = $score + $mstPvpMatchingScoreRange->getUpperRankMinScore();
        } elseif ($rankType === PvpMatchingType::Same) {
            $upperRangeScore = $score + $mstPvpMatchingScoreRange->getSameRankMaxScore();
            $lowerRangeScore = $score + $mstPvpMatchingScoreRange->getSameRankMinScore();
        } elseif ($rankType === PvpMatchingType::Lower) {
            $upperRangeScore = $score + $mstPvpMatchingScoreRange->getLowerRankMaxScore();
            $lowerRangeScore = $score + $mstPvpMatchingScoreRange->getLowerRankMinScore();
        }
        $matchRankRange = [];
        foreach ($formatedRanks as $formatedRank) {
            $rankStartScore = $formatedRank['startScoreRange'];
            $rankEndScore = $formatedRank['endScoreRange'];

            // 2つの範囲が重複する条件:
            // ランク範囲: [rankStartScore, rankEndScore]
            // 対象範囲: [lowerRangeScore, upperRangeScore]
            $hasOverlap = $rankStartScore <= $upperRangeScore && $rankEndScore >= $lowerRangeScore;

            if ($hasOverlap) {
                $matchRankRange[] = $formatedRank;
            }
        }

        return $matchRankRange;
    }

    /**
     * myIDのリストからランダムに1つのユーザーIDを選択する
     * @param array<string> $myIds myIDのリスト
     * @return string|null 選択されたmyID、またはリストが空の場合はnull
     */
    private function lotteryMyId(array $myIds): ?string
    {
        if (!$myIds) {
            return null;
        }

        // ランダムにユーザーIDを選択
        $randomIndex = array_rand($myIds);
        return $myIds[$randomIndex];
    }
}
