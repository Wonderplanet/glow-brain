<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Models\Contracts\OprMasterReleaseControlInterface;
use App\Domain\Resource\Traits\HasFactory;
use Carbon\Carbon;

// TODO： テーブル完全削除する。mngに切り替わっているため。
class OprMasterReleaseControl extends MstModel implements OprMasterReleaseControlInterface
{
    use HasFactory;

    protected $keyType = 'string';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    public $incrementing = false;
    public $timestamps = true;

    protected const MASTER_DATA_DB_PREFIX = 'mst_';

    protected $fillable = [
        'id',
        'release_key',
        'git_revision',
        'release_at',
        'release_description',
        'client_data_hash',
        'client_opr_data_hash',
        'zh-Hant_client_i18n_data_hash',
        'zh-Hant_client_opr_i18n_data_hash',
        'en_client_i18n_data_hash',
        'en_client_opr_i18n_data_hash',
        'ja_client_i18n_data_hash',
        'ja_client_opr_i18n_data_hash',
        'created_at',
        'updated_at',
    ];

    public function getReleaseKey(): int
    {
        return $this->release_key;
    }

    public function getGitRevision(): string
    {
        return $this->git_revision;
    }

    public function getClientDataHash(): string
    {
        return $this->client_data_hash;
    }

    public function setClientI18nDataHash(string $language, string $hash): void
    {
        $this->{$language . "_client_i18n_data_hash"} = $hash;
    }

    public function setClientOprI18nDataHash(string $language, string $hash): void
    {
        $this->{$language . "_client_opr_i18n_data_hash"} = $hash;
    }

    public function getClientI18nDataHashColumnName(string $language): string
    {
        return $language . "_client_i18n_data_hash";
    }

    public function getClientOprI18nDataHashColumnName(string $language): string
    {
        return $language . "_client_opr_i18n_data_hash";
    }

    public function getClientI18nDataHash(string $language): ?string
    {
        return $this->{$language . "_client_i18n_data_hash"};
    }

    public function getClientOprDataHash(): string
    {
        return $this->client_opr_data_hash;
    }

    public function getClientOprI18nDataHash(string $language): ?string
    {
        return $this->{$language . "_client_opr_i18n_data_hash"};
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(bool $enableEncryption): string
    {
        // 先頭に環境名を付ける
        $urlPrefix = config('app.env') . '/';

        // 暗号化が無効な場合はディレクトリを変更
        // $urlPrefix .= $enableEncryption ? '' : 'decrypted/';

        return $urlPrefix . $this->getReleaseKey() . '/' . $this->getGitRevision() . '/manifest.json';
    }

    public function getDbName(): string
    {
        return config('app.env') . '_' . self::MASTER_DATA_DB_PREFIX .
            $this->getReleaseKey() . '_' . $this->getGitRevision();
    }

    public function isRequireUpdate(int $releaseKey, string $hash): bool
    {
        return $this->release_key !== $releaseKey || $this->git_revision !== $hash;
    }

    public function isMstRequireUpdate(string $hash): bool
    {
        return $this->client_data_hash !== $hash;
    }

    public function isMstI18nRequireUpdate(string $hash, string $language): bool
    {
        $targetColumn = $this->getClientI18nDataHashColumnName($language);
        return $this->{$targetColumn} !== $hash;
    }

    public function isOprRequireUpdate(string $hash): bool
    {
        return $this->client_opr_data_hash !== $hash;
    }

    public function isOprI18nRequireUpdate(string $hash, string $language): bool
    {
        $targetColumn = $this->getClientOprI18nDataHashColumnName($language);
        return $this->{$targetColumn} !== $hash;
    }
}
