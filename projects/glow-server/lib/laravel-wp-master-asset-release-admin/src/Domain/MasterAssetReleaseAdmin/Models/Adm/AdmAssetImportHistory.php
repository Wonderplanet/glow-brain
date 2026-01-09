<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Adm;

use WonderPlanet\Domain\Admin\Models\BaseAdmModel;

/**
 * アセットデータインポート履歴テーブル
 *
 * @property string $id
 * @property string $git_revision
 * @property string $import_adm_user_id
 * @property string $import_source
 */
class AdmAssetImportHistory extends BaseAdmModel
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'git_revision' => 'string',
        'import_adm_user_id' => 'string',
        'import_source' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'git_revision',
        'import_adm_user_id',
        'import_source',
    ];

    public function getId(): string
    {
        return $this->id;
    }

    public function getGitRevision(): string
    {
        return $this->git_revision;
    }

    public function getImportAdmUserId(): string
    {
        return $this->import_adm_user_id;
    }

    public function getImportSource(): string
    {
        return $this->import_source;
    }
}
