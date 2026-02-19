<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Models\Contracts;

interface LogModelInterface
{
    public function makeModelKey(): string;

    public function isChanged(): bool;

    public function getId(): string;

    public function getUsrUserId(): string;

    public function setLogging(
        int $loggingNo,
        string $nginxRequestId,
        string $requestId,
    ): void;

    /**
     * @return array<mixed>
     */
    public function formatToInsert(): array;

    public function getLogTableName(): string;
}
