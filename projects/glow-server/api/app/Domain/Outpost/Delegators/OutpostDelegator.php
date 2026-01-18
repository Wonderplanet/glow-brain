<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Delegators;

use App\Domain\Outpost\Repositories\UsrOutpostEnhancementRepository;
use App\Domain\Outpost\Repositories\UsrOutpostRepository;
use App\Domain\Outpost\Services\UserOutpostService;
use App\Domain\Resource\Usr\Entities\UsrOutpostEntity;
use Illuminate\Support\Collection;

class OutpostDelegator
{
    public function __construct(
        private UserOutpostService $usrOutpostService,
        private UsrOutpostRepository $usrOutpostRepository,
        private UsrOutpostEnhancementRepository $usrOutpostEnhancementRepository,
    ) {
    }

    public function registerInitialOutpost(string $usrUserId): void
    {
        $this->usrOutpostService->registerInitialOutpost($usrUserId);
    }

    public function getUsedOutpost(string $usrUserId): ?UsrOutpostEntity
    {
        return $this->usrOutpostRepository->getUsed($usrUserId)?->toEntity();
    }

    public function getUsrOutpostEnhancementsByUsedOutpost(
        string $usrUserId
    ): Collection {
        return $this->usrOutpostService->getUsrOutpostEnhancementsByUsedOutpost($usrUserId);
    }

    /**
     * @param string $usrUserId
     * @return Collection
     */
    public function getOutpostEnhancements(string $usrUserId): Collection
    {
        return $this->usrOutpostEnhancementRepository->getList($usrUserId);
    }
}
