<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Services;

use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Pvp\Models\UsrPvpInterface;
use App\Domain\Pvp\Repositories\SysPvpSeasonRepository;
use App\Domain\Pvp\Repositories\UsrPvpRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRankRepository;
use App\Domain\Resource\Mst\Repositories\MstPvpRepository;
use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;
use App\Http\Responses\Data\PvpHeldStatusData;
use Carbon\CarbonImmutable;

class PvpTopService
{
    public function __construct(
        public readonly SysPvpSeasonRepository $sysPvpSeasonRepository,
        public readonly UsrPvpRepository $usrPvpRepository,
        public readonly MstPvpRepository $mstPvpRepository,
        public readonly MstPvpRankRepository $mstPvpRankRepository,
    ) {
    }

    public function getPvpHeldStatus(SysPvpSeasonEntity $sysPvpSeason): PvpHeldStatusData
    {
        return new PvpHeldStatusData(
            $sysPvpSeason->getId(),
            $this->sysPvpSeasonRepository->getHeldNumber($sysPvpSeason->getId()),
            $sysPvpSeason->getStartAt(),
            $sysPvpSeason->getEndAt(),
        );
    }

    /**
     * 最終プレイ情報をもとに新シーズンのUsrPvpを生成・復元し、適切なランクを設定する。
     *
     * @param string $usrUserId
     * @param string $sysPvpSeasonId
     * @param integer $maxDailyChallengeCount
     * @param integer $maxDailyItemChallengeCount
     * @param UsrPvpInterface|null $lastPlayedUsrPvp
     * @param CarbonImmutable $now
     * @return UsrPvpInterface
     */
    public function generateUsrPvpForNewSeason(
        string $usrUserId,
        string $sysPvpSeasonId,
        int $maxDailyChallengeCount,
        int $maxDailyItemChallengeCount,
        ?UsrPvpInterface $lastPlayedUsrPvp,
        CarbonImmutable $now,
    ): UsrPvpInterface {
        // 新シーズン用のUsrPvp基本データを生成
        $usrPvp = $this->usrPvpRepository->getOrMake(
            $usrUserId,
            $sysPvpSeasonId,
            $maxDailyChallengeCount,
            $maxDailyItemChallengeCount,
            $now
        );

        // 過去のプレイ履歴が存在しない場合は初期状態で返す
        if (!$lastPlayedUsrPvp) {
            $this->usrPvpRepository->syncModel($usrPvp);
            return $usrPvp;
        }

        // シーズン間隔をチェックして継承ランクを決定
        $countSeasonAfter = $this->sysPvpSeasonRepository->countSeasonsAfter(
            $sysPvpSeasonId,
            $lastPlayedUsrPvp->getSysPvpSeasonId(),
        );

        // 非アクティブ期間が長すぎる場合はデフォルトランクから開始
        $isWithinActiveRange = ($countSeasonAfter < PvpConstant::INACTIVE_SEASON_LIMIT);
        if (!$isWithinActiveRange) {
            $this->usrPvpRepository->syncModel($usrPvp);
            return $usrPvp;
        }

        // 継承ランクの計算と適用
        // 前回のランクから経過シーズン数に応じて降格
        $lastPlayedRankClassType = $lastPlayedUsrPvp->getPvpRankClassTypeEnum();
        $lastPlayedRankClassLevel = $lastPlayedUsrPvp->getPvpRankClassLevel();
        [
            $nextSeasonRankClassType,
            $nextSeasonRankClassLevel,
        ] = $lastPlayedRankClassType->getLowerWithLevel($countSeasonAfter, $lastPlayedRankClassLevel);

        // 継承ランクのマスターデータを取得
        $mstPvpRank = $this->mstPvpRankRepository->getByClassTypeAndLevel(
            $nextSeasonRankClassType->value,
            $nextSeasonRankClassLevel,
            true
        );

        // UsrPvpデータに継承ランク情報を反映
        $usrPvp->updatePvpRankClass(
            $nextSeasonRankClassType,
            $nextSeasonRankClassLevel,
        );
        $usrPvp->setScore($mstPvpRank->getRequiredLowerScore());

        // データベースに保存して返却
        $this->usrPvpRepository->syncModel($usrPvp);
        return $usrPvp;
    }
}
