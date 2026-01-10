<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Entities;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Common\Enums\Language;

/**
 * MngMasterReleaseVersionのエンティティクラス
 */
readonly class MngMasterReleaseVersionEntity
{
    private const MASTER_DATA_DB_PREFIX = 'mst_';

    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private string $gitRevision,
        readonly private string $masterSchemaVersion,
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
        readonly private ?CarbonImmutable $createdAt,
        readonly private ?CarbonImmutable $updatedAt,
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

    public function getMasterSchemaVersion(): string
    {
        return $this->masterSchemaVersion;
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

    public function getCreatedAt(): ?CarbonImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?CarbonImmutable
    {
        return $this->updatedAt;
    }

    /**
     * 言語をもとにマスターデータハッシュ値を取得
     *
     * @param Language $language
     * @return string
     */
    public function getClientMstDataI18nHashByLanguage(Language $language): string
    {
        return match ($language) {
            Language::Ja => $this->getClientMstDataI18nJaHash(),
            Language::En => $this->getClientMstDataI18nEnHash(),
            Language::Zh_Hant => $this->getClientMstDataI18nZhHash(),
        };
    }

    /**
     * 言語をもとにOprデータハッシュ値を取得
     *
     * @param Language $language
     * @return string
     */
    public function getClientOprDataI18nHashByLanguage(Language $language): string
    {
        return match ($language) {
            Language::Ja => $this->getClientOprDataI18nJaHash(),
            Language::En => $this->getClientOprDataI18nEnHash(),
            Language::Zh_Hant => $this->getClientOprDataI18nZhHash(),
        };
    }

    /**
     * envやDBの情報からDB名を取得する
     *
     * @return string
     */
    public function getDbName(): string
    {
        return config('app.env') . '_'
            . self::MASTER_DATA_DB_PREFIX
            . $this->getReleaseKey()
            . '_' . $this->getServerDbHash();
    }
}
