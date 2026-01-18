<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

interface GachaPrizeInterface
{
    public function getId(): string;

    public function getRarity(): string;
}
