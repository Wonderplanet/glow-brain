<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstDummyUserI18nEntity as Entity;

class MstDummyUserI18n extends MstModel
{
    protected $guarded = [];

    protected $table = 'mst_dummy_users_i18n';

    protected $casts = [
        'id' => 'string',
        'release_key' => 'integer',
        'mst_dummy_user_id' => 'string',
        'language' => 'string',
        'name' => 'string',
    ];

    public function toEntity(): Entity
    {
        return new Entity(
            $this->id,
            $this->mst_dummy_user_id,
            $this->language,
            $this->name,
        );
    }
}
