<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

/**
 * OprMasterReleaseVersionのエンティティクラス
 */
class OprMasterReleaseVersionEntity
{
    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private string $gitRevision,
        readonly private string $masterSchemeVersion,
        readonly private string $dataHash,
        readonly private string $serverDbHash,
        readonly private string $clientMstDataHash,
        readonly private string $clientMstDataI18nJaHash,
        readonly private string $clientMstDataI18nEnHash,
        readonly private string $clientMstDataI18nZhHash,
        readonly private string $clientOprDataHash,
        readonly private string $clientOprDataI18nJaHash,
        readonly private string $clientOprDataI18nEnHash,
        readonly private string $clientOprDataI18nZhHash,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getGitRevision(): string
    {
        return $this->gitRevision;
    }

    public function getMasterSchemeVersion(): string
    {
        return $this->masterSchemeVersion;
    }

    public function getDataHash(): string
    {
        return $this->dataHash;
    }

    public function getServerDbHash(): string
    {
        return $this->serverDbHash;
    }

    public function getClientMstDataHash(): string
    {
        return $this->clientMstDataHash;
    }

    public function getClientMstDataI18nJaHash(): string
    {
        return $this->clientMstDataI18nJaHash;
    }

    public function getClientMstDataI18nEnHash(): string
    {
        return $this->clientMstDataI18nEnHash;
    }

    public function getClientMstDataI18nZhHash(): string
    {
        return $this->clientMstDataI18nZhHash;
    }

    public function getClientOprDataHash(): string
    {
        return $this->clientOprDataHash;
    }

    public function getClientOprDataI18nJaHash(): string
    {
        return $this->clientOprDataI18nJaHash;
    }

    public function getClientOprDataI18nEnHash(): string
    {
        return $this->clientOprDataI18nEnHash;
    }

    public function getClientOprDataI18nZhHash(): string
    {
        return $this->clientOprDataI18nZhHash;
    }
}
