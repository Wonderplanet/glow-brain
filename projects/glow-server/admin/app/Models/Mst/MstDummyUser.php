<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstDummyUser as BaseMstDummyUser;

class MstDummyUser extends BaseMstDummyUser
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_dummy_user_i18n()
    {
        return $this->hasOne(MstDummyUserI18n::class, 'mst_dummy_user_id', 'id');
    }

    public function mst_dummy_outposts()
    {
        return $this->hasMany(MstDummyOutpost::class, 'mst_dummy_user_id', 'id');
    }

    public function mst_dummy_user_units()
    {
        return $this->hasMany(MstDummyUserUnit::class, 'mst_dummy_user_id', 'id');
    }

    public function mst_unit()
    {
        return $this->hasOne(MstUnit::class, 'id', 'mst_unit_id');
    }

    public function mst_emblem()
    {
        return $this->hasOne(MstEmblem::class, 'id', 'mst_emblem_id');
    }
}
