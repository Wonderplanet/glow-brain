<?php

declare(strict_types=1);

namespace App\Domain\Message\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Message\Models\UsrMessageInterface;
use App\Domain\Message\Repositories\UsrMessageRepository;
use App\Domain\Message\Services\UsrMessageService;
use App\Http\Responses\ResultData\MessageOpenResultData;

class OpenUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrMessageRepository $usrMessageRepository,
        private UsrMessageService $usrMessageService,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param array<int, string> $usrMessageIds
     * @return MessageOpenResultData
     * @throws \Throwable
     */
    public function exec(CurrentUser $user, array $usrMessageIds): MessageOpenResultData
    {
        $now = $this->clock->now();
        $updateUsrMessages = collect();
        $usrMessageIds = collect($usrMessageIds);
        $usrMessages = $this->usrMessageService->getByIdPlusRewardGroupIdData($user->id, $usrMessageIds, false);

        foreach ($usrMessages as $usrMessage) {
            /** @var UsrMessageInterface $usrMessage */
            if (!is_null($usrMessage->getOpenedAt())) {
                // すでに既読状態ならスキップ
                continue;
            }
            $usrMessage->setOpenedAt($now);
            $updateUsrMessages->push($usrMessage);
        }

        if ($updateUsrMessages->isEmpty()) {
            // 更新対象がなければ何もしない
            return new MessageOpenResultData();
        }
        try {
            $this->usrMessageRepository->syncModels($updateUsrMessages);
        } catch (\Exception $e) {
            throw new GameException(ErrorCode::FAILURE_UPDATE_BY_MESSAGE_OPENED_AT, $e->getMessage());
        }

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new MessageOpenResultData();
    }
}
