<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Encyclopedia\Models\UsrArtwork as BaseUsrArtwork;
use App\Models\Mst\MstArtwork;

class UsrArtwork extends BaseUsrArtwork
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_artwork()
    {
        return $this->belongsTo(MstArtwork::class, 'mst_artwork_id', 'id');
    }
}
