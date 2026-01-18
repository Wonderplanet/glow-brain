<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Encyclopedia\Models\UsrArtworkFragment as BaseUsrArtworkFragment;
use App\Models\Mst\MstArtworkFragment;

class UsrArtworkFragment extends BaseUsrArtworkFragment
{
    protected $connection = Database::TIDB_CONNECTION;

    public function mst_artwork_fragment()
    {
        return $this->belongsTo(MstArtworkFragment::class, 'mst_artwork_id', 'mst_artwork_id');
    }
}
