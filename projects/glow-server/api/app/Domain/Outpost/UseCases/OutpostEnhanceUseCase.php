<?php

declare(strict_types=1);

namespace App\Domain\Outpost\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Outpost\Repositories\LogOutpostEnhancementRepository;
use App\Domain\Outpost\Services\OutpostMissionTriggerService;
use App\Domain\Outpost\Services\UserOutpostService;
use App\Domain\Resource\Entities\LogTriggers\JoinLogTrigger;
use App\Domain\Resource\Mst\Repositories\MstOutpostEnhancementLevelRepository;
use App\Domain\Resource\Mst\Repositories\MstOutpostEnhancementRepository;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\OutpostEnhanceResultData;

class OutpostEnhanceUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private MstOutpostEnhancementRepository $mstOutpostEnhancementRepository,
        private MstOutpostEnhancementLevelRepository $mstOutpostEnhancementLevelRepository,
        private UserOutpostService $userOutpostService,
        private OutpostMissionTriggerService $outpostMissionTriggerService,
        private LogOutpostEnhancementRepository $logOutpostEnhancementRepository,
        // Delegator
        private UserDelegator $userDelegator,
    ) {
    }

    public function exec(CurrentUser $user, string $enhancementId, int $level): OutpostEnhanceResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $mstOutpostEnhancement = $this->mstOutpostEnhancementRepository->getById($enhancementId, true);

        $usrOutpostEnhancement = $this->userOutpostService->findUsrOutpostEnhancementByEnhancementId(
            $usrUserId,
            $mstOutpostEnhancement,
        );

        $currentLevel = $usrOutpostEnhancement->getLevel();
        if ($level <= $currentLevel) {
            throw new GameException(
                ErrorCode::INVALID_PARAMETER,
                sprintf('usr_outpost_enhancement level is invalid. current: %d, target: %d', $currentLevel, $level),
            );
        }

        $mstOutpostEnhancementLevels = $this->mstOutpostEnhancementLevelRepository->getLevelIsInRange(
            $enhancementId,
            $usrOutpostEnhancement->getLevel() + 1,
            $level,
            true,
        );

        $this->userOutpostService->setOutpostEnhancementLevel(
            $usrUserId,
            $enhancementId,
            $level
        );

        // ログ
        $logOutpostEnhancement = $this->logOutpostEnhancementRepository->create(
            $usrUserId,
            $enhancementId,
            $currentLevel,
            $level,
        );

        $costCoin = $mstOutpostEnhancementLevels->sum(fn($entity) => $entity->getCostCoin());
        $this->userDelegator->consumeCoin(
            $usrUserId,
            $costCoin,
            $now,
            new JoinLogTrigger($logOutpostEnhancement),
        );

        // ミッショントリガー送信
        $usrOutpostEnhancement = $this->userOutpostService->findUsrOutpostEnhancementByEnhancementId(
            $usrUserId,
            $mstOutpostEnhancement,
        );
        $this->outpostMissionTriggerService->sendEnhanceTrigger($usrOutpostEnhancement, $currentLevel);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new OutpostEnhanceResultData(
            $currentLevel,
            $level,
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($user->id)),
        );
    }
}
