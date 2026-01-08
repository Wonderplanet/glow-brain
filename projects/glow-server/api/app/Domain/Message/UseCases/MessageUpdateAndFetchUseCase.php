<?php

declare(strict_types=1);

namespace App\Domain\Message\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Message\Services\UsrMessageService;
use App\Http\Responses\ResultData\MessageUpdateAndFetchResultData;
use Carbon\CarbonImmutable;

class MessageUpdateAndFetchUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrMessageService $usrMessageService,
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user, string $language): MessageUpdateAndFetchResultData
    {
        $usrUserId = $user->getUsrUserId();
        $now = $this->clock->now();

        $gameStartAt = CarbonImmutable::parse($user->getGameStartAt());

        // add new message
        $this->usrMessageService->addNewMessages($usrUserId, $now, $language, $gameStartAt);
        $this->usrMessageService->addUsrMessagesForJumpPlusReward($usrUserId, $now);

        // fetch
        $messageData = $this->usrMessageService->getMessageData($usrUserId, $now, $language);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new MessageUpdateAndFetchResultData($messageData);
    }
}
