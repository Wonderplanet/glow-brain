<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Delegators;

use App\Domain\Emblem\Repositories\UsrEmblemRepository;
use App\Domain\Emblem\Services\EmblemService;
use Illuminate\Support\Collection;

class EmblemDelegator
{
    public function __construct(
        private EmblemService $emblemService,
        private UsrEmblemRepository $usrEmblemRepository,
    ) {
    }

    /**
     * @param string $usrUserId
     * @param string $mstEmblemId
     * @return void
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function validateHasUsrEmblem(string $usrUserId, string $mstEmblemId): void
    {
        $this->emblemService->validateHasUsrEmblem($usrUserId, $mstEmblemId);
    }

    /**
     * @param string $usrUserId
     * @return void
     */
    public function registerInitialEmblems(string $usrUserId): void
    {
        $this->emblemService->registerInitialEmblems($usrUserId);
    }

    /**
     * @param string $usrUserId
     * @param Collection<string> $mstEmblemIds
     * @return void
     */
    public function addUsrEmblems(string $usrUserId, Collection $mstEmblemIds): void
    {
        $this->emblemService->addUsrEmblems($usrUserId, $mstEmblemIds);
    }

    public function getChangedUsrEmblems(): Collection
    {
        return $this->usrEmblemRepository->getChangedModels();
    }

    /**
     * @param string $usrUserId
     * @param Collection<\App\Domain\Resource\Entities\Rewards\BaseReward> $rewards
     */
    public function convertDuplicatedEmblemToCoin(
        string $usrUserId,
        Collection $rewards,
    ): void {
        $this->emblemService->convertDuplicatedEmblemToCoin($usrUserId, $rewards);
    }

    /**
     * エンブレムの図鑑を取得済みにする
     * @param string $usrUserId
     * @param string $mstEmblemId
     */
    public function markAsCollected(string $usrUserId, string $mstEmblemId): void
    {
        $this->emblemService->markAsCollected($usrUserId, $mstEmblemId);
    }
}
