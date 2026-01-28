<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\Resource\Mst\Entities\MstIdleIncentiveEntity;
use Carbon\CarbonImmutable;

class IdleIncentiveService
{
    public function __construct()
    {
    }

    /**
     * 放置収益の通常受け取りが可能かどうかを確認する
     *
     * @param MstIdleIncentiveEntity $mstIdleIncentive
     * @param integer $elapsedMinutes
     * @return void
     * @throws GameException
     */
    public function validateReceivable(
        MstIdleIncentiveEntity $mstIdleIncentive,
        int $elapsedMinutes,
    ): void {

        if (
            $elapsedMinutes <= 0
            || $elapsedMinutes < $mstIdleIncentive->getInitialRewardReceiveMinutes()
        ) {
            throw new GameException(
                ErrorCode::IDLE_INCENTIVE_CANNOT_RECEIVE_REWARD,
                sprintf(
                    'The idle time is too short to receive any rewards. (minutes: %d)',
                    $elapsedMinutes
                ),
            );
        }
    }

    /**
     * 一次通貨使用でクイック探索の実行が可能かどうかを確認する
     * @param MstIdleIncentiveEntity $mstIdleIncentive
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @return void
     * @throws GameException
     */
    public function validateDiamondQuickReceivable(
        MstIdleIncentiveEntity $mstIdleIncentive,
        UsrIdleIncentiveInterface $usrIdleIncentive,
        int $addReceivableCount,
    ): void {
        $usrDiamondQuickReceiveCount = $usrIdleIncentive->getDiamondQuickReceiveCount();
        $mstMaxDailyDiamondQuickReceiveAmount = $mstIdleIncentive->getMaxDailyDiamondQuickReceiveAmount();
        if ($usrDiamondQuickReceiveCount >= $mstMaxDailyDiamondQuickReceiveAmount + $addReceivableCount) {
            throw new GameException(
                ErrorCode::IDLE_INCENTIVE_QUICK_RECEIVE_COUNT_LIMIT,
                'The maximum number of quick receive diamond has been reached.',
            );
        }
    }

    /**
     * 広告視聴でクイック探索の実行が可能かどうかを確認する
     *
     * @param MstIdleIncentiveEntity $mstIdleIncentive
     * @param UsrIdleIncentiveInterface $usrIdleIncentive
     * @return void
     * @throws GameException
     */
    public function validateAdQuickReceivable(
        MstIdleIncentiveEntity $mstIdleIncentive,
        UsrIdleIncentiveInterface $usrIdleIncentive,
        int $addReceivableCount
    ): void {
        $usrAdQuickReceiveCount = $usrIdleIncentive->getAdQuickReceiveCount();
        $mstMaxDailyAdQuickReceiveAmount = $mstIdleIncentive->getMaxDailyAdQuickReceiveAmount();
        if ($usrAdQuickReceiveCount >= $mstMaxDailyAdQuickReceiveAmount + $addReceivableCount) {
            throw new GameException(
                ErrorCode::IDLE_INCENTIVE_QUICK_RECEIVE_COUNT_LIMIT,
                'The maximum number of quick receive ad has been reached.',
            );
        }
    }

    /**
     * 放置時間を分単位で算出する。
     * 出力値の範囲は、0以上、最大放置時間以下となる。
     *
     * @param MstIdleIncentiveEntity $mstIdleIncentiveEntity
     * @param CarbonImmutable $idleStartedAt
     * @param CarbonImmutable $now
     * @return integer
     */
    public function calcElapsedTimeMinutes(
        MstIdleIncentiveEntity $mstIdleIncentiveEntity,
        CarbonImmutable $idleStartedAt,
        CarbonImmutable $now,
    ): int {

        // $now - $idleStartedAt で算出
        $diffMinutes = (int) $idleStartedAt->diffInMinutes($now, absolute: false);
        if ($diffMinutes < 0) {
            // 未来の時間が渡された場合は経過時間なしとみなす
            return 0;
        }

        return $this->clampIdleMinutes($mstIdleIncentiveEntity, $diffMinutes);
    }

    public function clampIdleMinutes(
        MstIdleIncentiveEntity $mstIdleIncentiveEntity,
        int $idleMinutes,
    ): int {
        return max(min($idleMinutes, $mstIdleIncentiveEntity->getMaxIdleHours() * 60), 0);
    }
}
