<?php

namespace App\Models\Mng;

use App\Domain\Resource\Mng\Models\MngMessage as BaseMngMessage;

class MngMessage extends BaseMngMessage
{
    public function mng_message_i18n()
    {
        return $this->hasOne(MngMessageI18n::class, 'mng_message_id', 'id');
    }

    public function mng_message_reward()
    {
        return $this->hasMany(MngMessageReward::class, 'mng_message_id', 'id');
    }
}
