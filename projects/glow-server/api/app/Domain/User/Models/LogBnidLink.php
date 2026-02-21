<?php

declare(strict_types=1);

namespace App\Domain\User\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;
use App\Domain\User\Enums\BnidLinkActionType;

/**
 * @property string $usr_user_id
 * @property string $action_type
 * @property ?string $before_bn_user_id
 * @property ?string $after_bn_user_id
 * @property ?string $usr_device_id
 * @property string $os_platform
 */
class LogBnidLink extends LogModel
{
    use HasFactory;

    public function setActionType(BnidLinkActionType $actionType): void
    {
        $this->action_type = $actionType->value;
    }

    public function setBeforeBnUserId(?string $beforeBnUserId): void
    {
        $this->before_bn_user_id = $beforeBnUserId;
    }

    public function setAfterBnUserId(?string $afterBnUserId): void
    {
        $this->after_bn_user_id = $afterBnUserId;
    }

    public function setUsrDeviceId(?string $usrDeviceId): void
    {
        $this->usr_device_id = $usrDeviceId;
    }

    public function setOsPlatform(string $osPlatform): void
    {
        $this->os_platform = $osPlatform;
    }
}
