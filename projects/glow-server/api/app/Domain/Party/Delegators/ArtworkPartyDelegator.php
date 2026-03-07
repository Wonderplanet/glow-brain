<?php

declare(strict_types=1);

namespace App\Domain\Party\Delegators;

use App\Domain\Party\Services\ArtworkPartyService;
use App\Domain\Resource\Entities\ArtworkPartyStatus;
use App\Domain\Resource\Usr\Entities\UsrArtworkPartyEntity;
use Illuminate\Support\Collection;

class ArtworkPartyDelegator
{
    public function __construct(
        private ArtworkPartyService $artworkPartyService,
    ) {
    }

    public function getUsrArtworkParty(string $usrUserId): UsrArtworkPartyEntity
    {
        return $this->artworkPartyService->getOrMakeDefault($usrUserId)->toEntity();
    }

    /**
     * @param Collection<array{mstArtworkId:string,gradeLevel:int}> $artworkPartyStatuses
     * @return Collection<ArtworkPartyStatus>
     */
    public function makeArtworkPartyStatusList(Collection $artworkPartyStatuses): Collection
    {
        return $this->artworkPartyService->makeArtworkPartyStatusList($artworkPartyStatuses);
    }
}
