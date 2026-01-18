<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\IdleIncentive\Repositories\UsrIdleIncentiveRepository;
use App\Domain\Resource\Mst\Repositories\MstQuestRepository;
use App\Domain\Resource\Mst\Repositories\MstStageRepository;
use App\Domain\Stage\Enums\QuestDifficulty;
use App\Domain\Stage\Enums\QuestType;
use Carbon\CarbonImmutable;

class UsrIdleIncentiveService
{
    public function __construct(
        private Clock $clock,
        private UsrIdleIncentiveRepository $usrIdleIncentiveRepository,
        private MstStageRepository $mstStageRepository,
        private MstQuestRepository $mstQuestRepository,
    ) {
    }

    /**
     * 一次通貨使用でクイック探索を実行した際のステータス変更処理
     *
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @param CarbonImmutable $now
     * @return void
     */
    public function diamondQuickReceive(
        UsrIdleIncentiveInterface $usrIdleIncentive,
        CarbonImmutable $now
    ): void {
        $usrIdleIncentive->incrementDiamondQuickReceiveCount();
        $usrIdleIncentive->setDiamondQuickReceiveAt($now->toDateTimeString());
    }

    /**
     * 一次通貨使用でクイック探索を実行できる回数を日跨ぎリセットする
     *
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @return void
     */
    public function resetDiamondQuickReceiveCount(
        UsrIdleIncentiveInterface $usrIdleIncentive,
    ): void {
        if ($this->clock->isFirstToday($usrIdleIncentive->getDiamondQuickReceiveAt())) {
            $usrIdleIncentive->setDiamondQuickReceiveCount(0);
        }
    }

    /**
     * 広告視聴でクイック探索を実行した際のステータス変更処理
     *
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @param CarbonImmutable $now
     * @return void
     */
    public function adQuickReceive(
        UsrIdleIncentiveInterface $usrIdleIncentive,
        CarbonImmutable $now
    ): void {
        $usrIdleIncentive->incrementAdQuickReceiveCount();
        $usrIdleIncentive->setAdQuickReceiveAt($now->toDateTimeString());
    }

    /**
     * 広告視聴でクイック探索を実行できる回数を日跨ぎリセットする
     *
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @return void
     */
    public function resetAdQuickReceiveCount(
        UsrIdleIncentiveInterface $usrIdleIncentive,
    ): void {
        if ($this->clock->isFirstToday($usrIdleIncentive->getAdQuickReceiveAt())) {
            $usrIdleIncentive->setAdQuickReceiveCount(0);
        }
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return void
     */
    public function resetReceiveCount(string $usrUserId, CarbonImmutable $now): void
    {
        $usrIdleIncentive = $this->usrIdleIncentiveRepository->findOrCreate($usrUserId, $now);

        $this->resetDiamondQuickReceiveCount($usrIdleIncentive);
        $this->resetAdQuickReceiveCount($usrIdleIncentive);

        $this->usrIdleIncentiveRepository->syncModel($usrIdleIncentive);
    }

    public function setIdleStartedAtNow(
        string $usrUserId,
        CarbonImmutable $now
    ): void {
        $usrIdleIncentive = $this->usrIdleIncentiveRepository->findOrCreate($usrUserId, $now);
        $usrIdleIncentive->setIdleStartedAt($now->toDateTimeString());
        $this->usrIdleIncentiveRepository->syncModel($usrIdleIncentive);
    }

    /**
     * ステージクリア時に探索報酬決定用のステージIDを更新する
     *
     * @param string $usrUserId
     * @param string $mstStageId
     * @param CarbonImmutable $now
     * @return void
     */
    public function updateRewardMstStageId(
        string $usrUserId,
        string $mstStageId,
        CarbonImmutable $now
    ): void {
        $mstStage = $this->mstStageRepository->getById($mstStageId);
        if (is_null($mstStage)) {
            // ステージが存在しない場合は何もしない
            return;
        }
        $mstQuest = $this->mstQuestRepository->getById($mstStage->getMstQuestId());
        if (is_null($mstQuest)) {
            // クエストが存在しない場合は何もしない
            return;
        }

        /**
         * 探索報酬内容が変わるのは下記の時のみ
         * - チュートリアルクエスト進捗時と
         * - ノーマル難易度のメインクエスト進捗時
         */
        if (
            (
                $mstQuest->getQuestType() === QuestType::NORMAL->value
                && $mstQuest->getDifficulty() === QuestDifficulty::NORMAL->value
            ) === false
            && $mstQuest->getQuestType() !== QuestType::TUTORIAL->value
        ) {
            // 対象外のステージなので何もしない
            return;
        }

        $usrIdleIncentive = $this->usrIdleIncentiveRepository->findOrCreate($usrUserId, $now);

        $currentMstStage = null;
        $currentRewardMstStageId = $usrIdleIncentive->getRewardMstStageId();
        if (is_null($currentRewardMstStageId) === false) {
            $currentMstStage = $this->mstStageRepository->getById($currentRewardMstStageId);
        }

        // 現在のreward_mst_stage_idがnullの場合は無条件で更新
        if (is_null($currentMstStage)) {
            $usrIdleIncentive->setRewardMstStageId($mstStageId);
            $this->usrIdleIncentiveRepository->syncModel($usrIdleIncentive);
            return;
        }

        $currentMstQuest = $this->mstQuestRepository->getById($currentMstStage->getMstQuestId());
        if (is_null($currentMstQuest)) {
            // mst_stagesはあるがmst_questsがないのは想定外のケース。
            // ステージクリア時に処理するメソッドで、クリア処理を完了させることを優先するために、
            // 何もせずに処理を終了する。
            return;
        }

        // 先に進んでいる方のステージIDをセットする
        if (
            ($mstStage->getSortOrder() > $currentMstStage->getSortOrder())
            || (
                // チュートリアル全クリア後にメインクエストを最初にクリアした時はsort_order比較なしで更新する。
                // クリア順的に、チュートリアル → メインクエスト の順になるため。
                $mstQuest->getQuestType() === QuestType::NORMAL->value
                && $currentMstQuest->getQuestType() === QuestType::TUTORIAL->value
            )
        ) {
            $usrIdleIncentive->setRewardMstStageId($mstStageId);
            $this->usrIdleIncentiveRepository->syncModel($usrIdleIncentive);
        }
    }
}
