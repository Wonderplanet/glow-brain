<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models\Contracts;

use Carbon\Carbon;

// TODO: クラスごと削除
interface OprMasterReleaseControlInterface
{
    public function getReleaseKey(): int;

    public function getGitRevision(): string;

    public function getClientDataHash(): string;

    public function setClientI18nDataHash(string $language, string $hash): void;

    public function setClientOprI18nDataHash(string $language, string $hash): void;

    public function getClientI18nDataHash(string $language): ?string;

    public function getClientOprDataHash(): string;

    public function getClientOprI18nDataHash(string $language): ?string;

    public function getUpdatedAt(): Carbon;

    public function getCreatedAt(): Carbon;

    public function getUrl(bool $enableEncryption): string;

    public function getDbName(): string;

    public function isRequireUpdate(int $releaseKey, string $hash): bool;

    public function isMstRequireUpdate(string $hash): bool;

    public function isMstI18nRequireUpdate(string $hash, string $language): bool;

    public function isOprRequireUpdate(string $hash): bool;

    public function isOprI18nRequireUpdate(string $hash, string $language): bool;
}
