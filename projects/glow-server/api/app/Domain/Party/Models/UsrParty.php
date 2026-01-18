<?php

declare(strict_types=1);

namespace App\Domain\Party\Models;

use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\UsrPartyInterface;
use App\Domain\Resource\Usr\Entities\UsrPartyEntity;
use App\Domain\Resource\Usr\Models\UsrModel;
use Illuminate\Support\Collection;

class UsrParty extends UsrModel implements UsrPartyInterface
{
    protected static string $tableName = 'usr_parties';
    protected array $modelKeyColumns = ['usr_user_id', 'party_no'];

    /**
     * @param Collection<string> $usrUnitIds
     */
    public static function create(string $usrUserId, int $partyNo, Collection $usrUnitIds): UsrPartyInterface
    {
        $attributes = [];
        $attributes['usr_user_id'] = $usrUserId;
        $attributes['party_no'] = $partyNo;
        $attributes['party_name'] = "パーティ$partyNo";
        for ($i = 1; $i <= PartyConstant::MAX_UNIT_COUNT_IN_PARTY; $i++) {
            $columnName = "usr_unit_id_$i";
            $attributes[$columnName] = $usrUnitIds->get($i - 1, null); // 未設定の場合はnull
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
     * @param Collection<string> $usrUnitIds
     */
    public function setUnits(Collection $usrUnitIds): void
    {
        for ($i = 1; $i <= PartyConstant::MAX_UNIT_COUNT_IN_PARTY; $i++) {
            $columnName = "usr_unit_id_$i";
            $this->attributes[$columnName] = $usrUnitIds->get($i - 1, null);
        }
    }

    public function getUsrUnitId1(): string
    {
        return $this->attributes['usr_unit_id_1'];
    }

    public function getUsrUnitId2(): ?string
    {
        return $this->attributes['usr_unit_id_2'];
    }

    public function getUsrUnitId3(): ?string
    {
        return $this->attributes['usr_unit_id_3'];
    }

    public function getUsrUnitId4(): ?string
    {
        return $this->attributes['usr_unit_id_4'];
    }

    public function getUsrUnitId5(): ?string
    {
        return $this->attributes['usr_unit_id_5'];
    }

    public function getUsrUnitId6(): ?string
    {
        return $this->attributes['usr_unit_id_6'];
    }

    public function getUsrUnitId7(): ?string
    {
        return $this->attributes['usr_unit_id_7'];
    }

    public function getUsrUnitId8(): ?string
    {
        return $this->attributes['usr_unit_id_8'];
    }

    public function getUsrUnitId9(): ?string
    {
        return $this->attributes['usr_unit_id_9'];
    }

    public function getUsrUnitId10(): ?string
    {
        return $this->attributes['usr_unit_id_10'];
    }

    /**
     * @return Collection<string>
     */
    public function getUsrUnitIds(): Collection
    {
        return collect([
            $this->getUsrUnitId1(),
            $this->getUsrUnitId2(),
            $this->getUsrUnitId3(),
            $this->getUsrUnitId4(),
            $this->getUsrUnitId5(),
            $this->getUsrUnitId6(),
            $this->getUsrUnitId7(),
            $this->getUsrUnitId8(),
            $this->getUsrUnitId9(),
            $this->getUsrUnitId10(),
        ])->filter(fn($usrUnitId) => $usrUnitId !== null);
    }

    public function toEntity(): UsrPartyEntity
    {
        return new UsrPartyEntity(
            $this->getId(),
            $this->getUsrUserId(),
            $this->getPartyNo(),
            $this->getPartyName(),
            $this->getUsrUnitId1(),
            $this->getUsrUnitId2(),
            $this->getUsrUnitId3(),
            $this->getUsrUnitId4(),
            $this->getUsrUnitId5(),
            $this->getUsrUnitId6(),
            $this->getUsrUnitId7(),
            $this->getUsrUnitId8(),
            $this->getUsrUnitId9(),
            $this->getUsrUnitId10(),
        );
    }
}
