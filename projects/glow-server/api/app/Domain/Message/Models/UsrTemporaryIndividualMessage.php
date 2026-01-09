<?php

declare(strict_types=1);

namespace App\Domain\Message\Models;

use App\Domain\Resource\Traits\HasFactory;
use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property string $mng_message_id
 */
class UsrTemporaryIndividualMessage extends UsrEloquentModel implements UsrTemporaryIndividualMessageInterface
{
    use HasFactory;

    protected $fillable = [
        'usr_user_id',
        'mng_message_id',
    ];

    public function getUsrUserId(): string
    {
        return $this->usr_user_id;
    }

    public function setUsrUserId(string $usrUserId): void
    {
        $this->usr_user_id = $usrUserId;
    }

    public function getMngMessageId(): string
    {
        return $this->mng_message_id;
    }

    public function setMngMessageId(string $mngMessageId): void
    {
        $this->mng_message_id = $mngMessageId;
    }
}
