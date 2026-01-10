<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $os_platform
 */
class UsrOsPlatform extends UsrEloquentModel implements UsrOsPlatformInterface
{
    use HasFactory;

    protected $table = 'usr_os_platforms';

    protected $guarded = [
    ];

    protected $casts = [
    ];

    /**
     * UsrModelManagerでキャッシュ管理する際に使うユニークキーを作成する
     */
    public function makeModelKey(): string
    {
        return $this->usr_user_id . $this->os_platform;
    }

    public function getOsPlatform(): string
    {
        return $this->os_platform;
    }

    public function setOsPlatform(string $osPlatform): void
    {
        $this->os_platform = $osPlatform;
    }
}
