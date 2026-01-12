<?php

namespace App\Models\Mst;

use App\Constants\Database;
use App\Domain\Resource\Mst\Models\MstFragmentBox as BaseMstFragmentBox;

class MstFragmentBox extends BaseMstFragmentBox
{
    protected $connection = Database::MASTER_DATA_CONNECTION;

    public function mst_fragment_box_groups()
    {
        return $this->hasMany(
            MstFragmentBoxGroup::class,
            'mst_fragment_box_group_id',
            'mst_fragment_box_group_id',
        );
    }
}
