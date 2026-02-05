<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $usr_user_id
 * @property string $mst_tutorial_id
 */
class UsrTutorial extends UsrEloquentModel implements UsrTutorialInterface
{
    use HasFactory;

    protected $fillable = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->mst_tutorial_id;
    }

    public function getMstTutorialId(): string
    {
        return $this->mst_tutorial_id;
    }
}
