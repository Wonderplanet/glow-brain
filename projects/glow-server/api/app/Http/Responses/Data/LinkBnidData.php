<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

readonly class LinkBnidData
{
    public function __construct(
        private ?string $idToken,
        private string $bnidLinkedAt,
    ) {
    }

    public function getIdToken(): ?string
    {
        return $this->idToken;
    }

    public function getBnidLinkedAt(): string
    {
        return $this->bnidLinkedAt;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'idToken' => $this->idToken,
            'bnidLinkedAt' => $this->bnidLinkedAt,
        ];
    }
}
