<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Outpost\Models\UsrOutpost as BaseUsrOutpost;
use App\Models\Mst\MstArtwork;

class UsrOutpost extends BaseUsrOutpost
{
    protected $connection = Database::TIDB_CONNECTION;

    public function usr_outpost_enhancement()
    {
        return $this->hasMany(UsrOutpostEnhancement::class, 'mst_outpost_id', 'mst_outpost_id');
    }

    public function mst_artwork()
    {
        return $this->belongsTo(MstArtwork::class, 'mst_artwork_id', 'id');
    }
}
