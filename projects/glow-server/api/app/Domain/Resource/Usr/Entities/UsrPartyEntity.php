<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrPartyEntity
{
    public function __construct(
        private string $id,
        private string $usr_user_id,
        private int $party_no,
        private string $party_name,
        private string $usr_unit_id_1,
        private ?string $usr_unit_id_2,
        private ?string $usr_unit_id_3,
        private ?string $usr_unit_id_4,
        private ?string $usr_unit_id_5,
        private ?string $usr_unit_id_6,
        private ?string $usr_unit_id_7,
        private ?string $usr_unit_id_8,
        private ?string $usr_unit_id_9,
        private ?string $usr_unit_id_10,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function getPartyNo(): int
    {
        return $this->party_no;
    }

    public function getPartyName(): string
    {
        return $this->party_name;
    }

    public function getUsrUnitId1(): string
    {
        return $this->usr_unit_id_1;
    }

    public function getUsrUnitId2(): ?string
    {
        return $this->usr_unit_id_2;
    }

    public function getUsrUnitId3(): ?string
    {
        return $this->usr_unit_id_3;
    }

    public function getUsrUnitId4(): ?string
    {
        return $this->usr_unit_id_4;
    }

    public function getUsrUnitId5(): ?string
    {
        return $this->usr_unit_id_5;
    }

    public function getUsrUnitId6(): ?string
    {
        return $this->usr_unit_id_6;
    }

    public function getUsrUnitId7(): ?string
    {
        return $this->usr_unit_id_7;
    }

    public function getUsrUnitId8(): ?string
    {
        return $this->usr_unit_id_8;
    }

    public function getUsrUnitId9(): ?string
    {
        return $this->usr_unit_id_9;
    }

    public function getUsrUnitId10(): ?string
    {
        return $this->usr_unit_id_10;
    }
}
