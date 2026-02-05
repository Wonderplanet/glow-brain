<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Outpost\Constants\OutpostConstant;
use App\Domain\Outpost\Models\UsrOutpostEnhancementInterface;
use App\Domain\Outpost\Models\UsrOutpostInterface;
use App\Domain\Outpost\Repositories\UsrOutpostEnhancementRepository;
use App\Domain\Outpost\Repositories\UsrOutpostRepository;
use App\Domain\Resource\Mst\Entities\MstOutpostEnhancementEntity;
use Illuminate\Support\Collection;

class UserOutpostService
{
    public function __construct(
        private UsrOutpostRepository $usrOutpostRepository,
        private UsrOutpostEnhancementRepository $usrOutpostEnhancementRepository,
    ) {
    }

    public function findUsrOutpostEnhancementByEnhancementId(
        string $usrUserId,
        MstOutpostEnhancementEntity $mstOutpostEnhancementEntity,
    ): UsrOutpostEnhancementInterface {
        $mstOutpostId = $mstOutpostEnhancementEntity->getMstOutpostId();
        $mstOutpostEnhancementId = $mstOutpostEnhancementEntity->getId();
        $usrOutpostEnhancement = $this->usrOutpostEnhancementRepository->findByEnhancementId(
            $usrUserId,
            $mstOutpostEnhancementId,
        );

        if (is_null($usrOutpostEnhancement)) {
            $usrOutpostEnhancement = $this->usrOutpostEnhancementRepository->create(
                $usrUserId,
                $mstOutpostId,
                $mstOutpostEnhancementEntity->getId(),
            );
        }

        return $usrOutpostEnhancement;
    }

    public function setOutpostEnhancementLevel(
        string $usrUserId,
        string $enhancementId,
        int $level
    ): void {
        $usrOutpostEnhancement = $this->usrOutpostEnhancementRepository->findByEnhancementId(
            $usrUserId,
            $enhancementId
        );
        if (is_null($usrOutpostEnhancement)) {
            throw new GameException(ErrorCode::INVALID_PARAMETER);
        }
        $usrOutpostEnhancement->setLevel($level);
        $this->usrOutpostEnhancementRepository->syncModel($usrOutpostEnhancement);
    }

    /**
     * @param string $usrUserId
     * @param string $mstOutpostId
     * @param string|null $mstArtworkId
     * @return UsrOutpostInterface
     * @throws GameException
     */
    public function setArtwork(
        string $usrUserId,
        string $mstOutpostId,
        ?string $mstArtworkId
    ): UsrOutpostInterface {
        $usrOutpost = $this->usrOutpostRepository->findByMstOutpostId($usrUserId, $mstOutpostId, true);

        $usrOutpost->setMstArtworkId($mstArtworkId);
        $this->usrOutpostRepository->syncModel($usrOutpost);

        return $usrOutpost;
    }

    public function registerInitialOutpost(string $usrUserId): void
    {
        $this->usrOutpostRepository->create($usrUserId, OutpostConstant::INITIAL_OUTPOST_ID, 1);
    }

    public function getUsrOutpostEnhancementsByUsedOutpost(string $usrUserId): Collection
    {
        $usrOutpost = $this->usrOutpostRepository->getUsed($usrUserId);
        if (is_null($usrOutpost)) {
            return collect();
        }
        return $this->usrOutpostEnhancementRepository->getByMstOutpostId($usrUserId, $usrOutpost->getMstOutpostId());
    }
}
