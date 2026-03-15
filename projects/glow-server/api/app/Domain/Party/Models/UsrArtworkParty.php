<?php

declare(strict_types=1);

namespace App\Domain\Party\Models;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\UsrArtworkPartyInterface;
use App\Domain\Resource\Usr\Entities\UsrArtworkPartyEntity;
use App\Domain\Resource\Usr\Models\UsrModel;
use Illuminate\Support\Collection;

class UsrArtworkParty extends UsrModel implements UsrArtworkPartyInterface
{
    protected static string $tableName = 'usr_artwork_parties';
    protected array $modelKeyColumns = ['usr_user_id', 'party_no'];

    /**
     * @param Collection<string> $mstArtworkIds
     */
    public static function create(string $usrUserId, int $partyNo, Collection $mstArtworkIds): UsrArtworkPartyInterface
    {
        $attributes = [];
        $attributes['usr_user_id'] = $usrUserId;
        $attributes['party_no'] = $partyNo;
        $attributes['party_name'] = "パーティ$partyNo";
        for ($i = 1; $i <= PartyConstant::MAX_ARTWORK_COUNT_IN_PARTY; $i++) {
            $columnName = "mst_artwork_id_$i";
            $attributes[$columnName] = $mstArtworkIds->get($i - 1, null); // 未設定の場合はnull
        }

        return new self($attributes);
    }

    public function getPartyNo(): int
    {
        return $this->attributes['party_no'];
    }

    public function getPartyName(): string
    {
        return $this->attributes['party_name'];
    }

    public function setPartyName(string $partyName): void
    {
        $this->attributes['party_name'] = $partyName;
    }

    /**
     * @param Collection<string> $mstArtworkIds
     */
    public function setArtworks(Collection $mstArtworkIds): void
    {
        if ($mstArtworkIds->isEmpty()) {
            throw new GameException(
                ErrorCode::PARTY_INVALID_ARTWORK_COUNT,
                'Artwork party artworks is empty'
            );
        }

        for ($i = 1; $i <= PartyConstant::MAX_ARTWORK_COUNT_IN_PARTY; $i++) {
            $columnName = "mst_artwork_id_$i";
            $this->attributes[$columnName] = $mstArtworkIds->get($i - 1, null);
        }
    }

    public function getMstArtworkId1(): string
    {
        return $this->attributes['mst_artwork_id_1'];
    }

    public function getMstArtworkId2(): ?string
    {
        return $this->attributes['mst_artwork_id_2'];
    }

    public function getMstArtworkId3(): ?string
    {
        return $this->attributes['mst_artwork_id_3'];
    }

    public function getMstArtworkId4(): ?string
    {
        return $this->attributes['mst_artwork_id_4'];
    }

    public function getMstArtworkId5(): ?string
    {
        return $this->attributes['mst_artwork_id_5'];
    }

    public function getMstArtworkId6(): ?string
    {
        return $this->attributes['mst_artwork_id_6'];
    }

    public function getMstArtworkId7(): ?string
    {
        return $this->attributes['mst_artwork_id_7'];
    }

    public function getMstArtworkId8(): ?string
    {
        return $this->attributes['mst_artwork_id_8'];
    }

    public function getMstArtworkId9(): ?string
    {
        return $this->attributes['mst_artwork_id_9'];
    }

    public function getMstArtworkId10(): ?string
    {
        return $this->attributes['mst_artwork_id_10'];
    }

    /**
     * @return Collection<string>
     */
    public function getMstArtworkIds(): Collection
    {
        return collect([
            $this->getMstArtworkId1(),
            $this->getMstArtworkId2(),
            $this->getMstArtworkId3(),
            $this->getMstArtworkId4(),
            $this->getMstArtworkId5(),
            $this->getMstArtworkId6(),
            $this->getMstArtworkId7(),
            $this->getMstArtworkId8(),
            $this->getMstArtworkId9(),
            $this->getMstArtworkId10(),
        ])->filter(fn($mstArtworkId) => StringUtil::isSpecified($mstArtworkId));
    }

    public function toEntity(): UsrArtworkPartyEntity
    {
        return new UsrArtworkPartyEntity(
            $this->getId(),
            $this->getUsrUserId(),
            $this->getPartyNo(),
            $this->getPartyName(),
            $this->getMstArtworkId1(),
            $this->getMstArtworkId2(),
            $this->getMstArtworkId3(),
            $this->getMstArtworkId4(),
            $this->getMstArtworkId5(),
            $this->getMstArtworkId6(),
            $this->getMstArtworkId7(),
            $this->getMstArtworkId8(),
            $this->getMstArtworkId9(),
            $this->getMstArtworkId10(),
        );
    }
}
