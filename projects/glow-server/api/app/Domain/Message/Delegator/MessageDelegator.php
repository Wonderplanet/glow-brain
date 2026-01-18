<?php

declare(strict_types=1);

namespace App\Domain\Message\Delegator;

use App\Domain\Message\Services\UsrMessageService;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use Carbon\CarbonImmutable;

class MessageDelegator
{
    public function __construct(
        private UsrMessageService $usrMessageService
    ) {
    }

    /**
     * 新規メッセージ登録
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @param string $language
     * @param CarbonImmutable $gameStartAt
     * @return int
     */
    public function addNewMessages(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): int {
        return $this->usrMessageService->addNewMessages($usrUserId, $now, $language, $gameStartAt);
    }

    public function addNewSystemMessage(
        string $usrUserId,
        ?string $rewardGroupId,
        ?CarbonImmutable $expiredAt,
        BaseReward $reward,
        string $title,
        string $body,
        ?string $prefixMessageSource = null,
    ): void {
        $this->usrMessageService->addNewSystemMessage(
            $usrUserId,
            $rewardGroupId,
            $expiredAt,
            $reward,
            $title,
            $body,
            $prefixMessageSource,
        );
    }

    /**
     * 未読・未登録メッセージカウントの取得
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @param string $language
     * @param CarbonImmutable $gameStartAt
     * @return int
     */
    public function getUnopenedMessageCount(
        string $usrUserId,
        CarbonImmutable $now,
        string $language,
        CarbonImmutable $gameStartAt
    ): int {
        return $this->usrMessageService->getUnopenedMessageCount($usrUserId, $now, $language, $gameStartAt);
    }
}
