<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities\Rewards;

use App\Domain\Resource\Dtos\LogTriggerDto;

/**
 * PVP報酬の共通親クラス
 */
abstract class PvpReward extends BaseReward
{
    /**
     * 報酬グループID
     */
    protected string $rewardGroupId;

    /**
     * シーズンID
     */
    protected string $sysPvpSeasonId;

    public function __construct(
        string $resourceType,
        ?string $resourceId,
        int $amount,
        LogTriggerDto $logTriggerDto,
        string $rewardGroupId,
        string $sysPvpSeasonId,
    ) {
        parent::__construct(
            $resourceType,
            $resourceId,
            $amount,
            $logTriggerDto,
        );
        $this->rewardGroupId = $rewardGroupId;
        $this->sysPvpSeasonId = $sysPvpSeasonId;
    }

    public function getRewardGroupId(): string
    {
        return $this->rewardGroupId . '_' . $this->sysPvpSeasonId;
    }

    /**
     * 報酬の有効期限（日数）を取得
     *
     * @return int
     */
    abstract public function getExpirationDays(): int;

    /**
     * メッセージのタイトルを取得
     *
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     * メッセージの本文を取得
     *
     * @return string
     */
    abstract public function getBody(): string;
}
