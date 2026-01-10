<?php

namespace App\Models\Mng;

use App\Domain\Resource\Mng\Models\MngMessageI18n as BaseMngMessageI18n;

class MngMessageI18n extends BaseMngMessageI18n
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mngMessage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MngMessage::class);
    }
}
