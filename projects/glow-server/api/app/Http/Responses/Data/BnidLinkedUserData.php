<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

readonly class BnidLinkedUserData
{
    public function __construct(
        private ?string $name,
        private ?int $level,
        private ?string $myId,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function getMyId(): ?string
    {
        return $this->myId;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'name' => $this->name,
            'level' => $this->level,
            'myId' => $this->myId,
        ];
    }
}
