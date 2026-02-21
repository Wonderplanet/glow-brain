<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Resource\Usr\Entities\UsrArtworkPartyEntity;

class ArtworkPartySaveResultData
{
    public function __construct(
        public UsrArtworkPartyEntity $usrArtworkParty,
    ) {
    }
}
