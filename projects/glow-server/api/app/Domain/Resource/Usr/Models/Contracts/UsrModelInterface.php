<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Models\Contracts;

interface UsrModelInterface
{
    public function makeModelKey(): string;

    public function isChanged(): bool;

    public function getId(): string;

    public function getUsrUserId(): string;

    public function syncOriginal(): static;
}
