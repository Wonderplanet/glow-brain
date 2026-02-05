<?php

declare(strict_types=1);

namespace App\Domain\Party\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\UsrArtworkPartyInterface;
use App\Domain\Party\Repositories\UsrArtworkPartyRepository;
use App\Domain\Resource\Entities\ArtworkPartyStatus;
use App\Domain\Resource\Mst\Services\MstConfigService;
use App\Domain\Resource\Usr\Entities\UsrArtworkPartyEntity;
use Illuminate\Support\Collection;

class ArtworkPartyService
{
    public function __construct(
        private UsrArtworkPartyRepository $usrArtworkPartyRepository,
        private EncyclopediaDelegator $encyclopediaDelegator,
        private MstConfigService $mstConfigService,
    ) {
    }

    /**
     * @param array<string> $mstArtworkIds
     */
    public function saveParty(string $usrUserId, array $mstArtworkIds): UsrArtworkPartyEntity
    {
        $mstArtworkIdCollection = collect($mstArtworkIds);

        // バリデーション
        $this->validateArtworkIds($mstArtworkIdCollection);
        $this->validateOwned($usrUserId, $mstArtworkIdCollection);

        $usrArtworkParty = $this->usrArtworkPartyRepository->get($usrUserId);
        if (is_null($usrArtworkParty)) {
            $usrArtworkParty = $this->usrArtworkPartyRepository->create(
                $usrUserId,
                1,
                $mstArtworkIdCollection,
            );
        } else {
            $usrArtworkParty->setArtworks($mstArtworkIdCollection);
            $this->usrArtworkPartyRepository->syncModel($usrArtworkParty);
        }
        return $usrArtworkParty->toEntity();
    }

    public function getOrMakeDefault(string $usrUserId): UsrArtworkPartyInterface
    {
        $usrArtworkParty = $this->usrArtworkPartyRepository->get($usrUserId);
        if (is_null($usrArtworkParty)) {
            $usrArtworkParty = $this->makeDefaultArtworkParty($usrUserId);
        }
        return $usrArtworkParty;
    }

    private function makeDefaultArtworkParty(string $usrUserId, int $partyNo = 1): UsrArtworkPartyInterface
    {
        $defaultArtworkId = $this->mstConfigService->getDefaultArtworkPartyArtworkId();
        return $this->usrArtworkPartyRepository->create(
            $usrUserId,
            $partyNo,
            collect([$defaultArtworkId]),
        );
    }

    /**
     * 編成する原画IDの数と重複を検証する
     *
     * @throws GameException
     */
    private function validateArtworkIds(Collection $mstArtworkIds): void
    {
        // 空チェック
        if ($mstArtworkIds->isEmpty()) {
            throw new GameException(
                ErrorCode::PARTY_INVALID_ARTWORK_COUNT,
                'Artwork party artworks is empty'
            );
        }

        // 重複チェック
        $uniqueCount = $mstArtworkIds->unique()->count();
        if ($uniqueCount < $mstArtworkIds->count()) {
            throw new GameException(
                ErrorCode::PARTY_DUPLICATE_ARTWORK_ID,
                'Artwork party artworks duplicate'
            );
        }

        // 最大数チェック
        if ($mstArtworkIds->count() > PartyConstant::MAX_ARTWORK_COUNT_IN_PARTY) {
            throw new GameException(
                ErrorCode::PARTY_INVALID_ARTWORK_COUNT,
                'Artwork party artworks over max count'
            );
        }
    }

    /**
     * 編成する原画を所持しているかを検証する
     *
     * @throws GameException
     */
    private function validateOwned(string $usrUserId, Collection $mstArtworkIds): void
    {
        if ($mstArtworkIds->isEmpty()) {
            return;
        }

        // 所持している原画IDを取得
        $ownedArtworkIds = $this->encyclopediaDelegator
            ->getUsrArtworksByMstArtworkIds(
                $usrUserId,
                $mstArtworkIds
            )
            ->map(fn($usrArtwork) => $usrArtwork->getMstArtworkId());

        // 所持していない原画が含まれていたらエラー
        $notOwnedIds = $mstArtworkIds->diff($ownedArtworkIds);
        if ($notOwnedIds->isNotEmpty()) {
            throw new GameException(
                ErrorCode::PARTY_INVALID_ARTWORK_ID,
                'Artwork party artworks not owned'
            );
        }
    }

    /**
     * @param Collection<array> $artworkPartyStatuses
     * @return Collection<ArtworkPartyStatus>
     */
    public function makeArtworkPartyStatusList(Collection $artworkPartyStatuses): Collection
    {
        return $artworkPartyStatuses->map(function (array $partyStatus) {
            return new ArtworkPartyStatus(
                $partyStatus['mstArtworkId'],
                $partyStatus['gradeLevel'],
            );
        });
    }
}
