<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Common\Repositories\LogAdFreePlayRepository;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use Carbon\CarbonImmutable;

/**
 * 広告視聴関連の処理を共通化するためのサービスクラス
 */
class AdPlayService
{
    public function __construct(
        // Repository
        private LogAdFreePlayRepository $logAdFreePlayRepository,
        // Delegator
        private MissionDelegator $missionDelegator,
    ) {
    }

    /**
     * 広告視聴した時に呼び出すメソッド
     */
    public function adPlay(
        string $usrUserId,
        string $contentType,
        string $targetId,
        CarbonImmutable $now,
    ): void {
        // 広告視聴のトリガーを送信
        $this->sendIaaCountTrigger();

        // ログ保存
        $this->logAdFreePlayRepository->create(
            $usrUserId,
            $contentType,
            $targetId,
            $now,
        );
    }

    /**
     * IaaCountのミッショントリガーを送信する
     * @return void
     */
    private function sendIaaCountTrigger(): void
    {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::IAA_COUNT->value,
                null,
                1,
            )
        );
    }
}
