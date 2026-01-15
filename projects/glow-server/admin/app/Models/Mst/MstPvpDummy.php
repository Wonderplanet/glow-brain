<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstPvpDummy as BaseMstPvpDummy;

class MstPvpDummy extends BaseMstPvpDummy
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_dummy_user()
    {
        return $this->hasOne(MstDummyUser::class, 'id', 'mst_dummy_user_id');
    }
}
