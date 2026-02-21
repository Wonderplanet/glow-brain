<?php

declare(strict_types=1);

namespace App\Domain\Party\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Party\Services\ArtworkPartyService;
use App\Http\Responses\ResultData\ArtworkPartySaveResultData;

class ArtworkPartySaveUseCase
{
    use UseCaseTrait;

    public function __construct(
        private ArtworkPartyService $artworkPartyService,
    ) {
    }

    /**
     * @param array<string> $artworkParty mst_artworks.idの配列
     * @throws \Throwable
     */
    public function exec(CurrentUser $user, array $artworkParty): ArtworkPartySaveResultData
    {
        $usrUserId = $user->getUsrUserId();
        $usrArtworkParty = $this->artworkPartyService->saveParty($usrUserId, $artworkParty);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new ArtworkPartySaveResultData($usrArtworkParty);
    }
}
