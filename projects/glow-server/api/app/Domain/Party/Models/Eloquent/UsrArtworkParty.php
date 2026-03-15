<?php

declare(strict_types=1);

namespace App\Domain\Party\Models\Eloquent;

use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property int $party_no
 * @property string $party_name
 * @property string $mst_artwork_id_1
 * @property string|null $mst_artwork_id_2
 * @property string|null $mst_artwork_id_3
 * @property string|null $mst_artwork_id_4
 * @property string|null $mst_artwork_id_5
 * @property string|null $mst_artwork_id_6
 * @property string|null $mst_artwork_id_7
 * @property string|null $mst_artwork_id_8
 * @property string|null $mst_artwork_id_9
 * @property string|null $mst_artwork_id_10
 */
class UsrArtworkParty extends UsrEloquentModel
{
    protected $table = 'usr_artwork_parties';

    /**
     * getMstArtworkId{数値}メソッドが呼ばれたら、mst_artwork_id_{数値}を返す
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^getMstArtworkId(\d+)$/', $method, $matches)) {
            $index = (int)$matches[1];
            return $this->attributes["mst_artwork_id_$index"];
        }
        return parent::__call($method, $parameters);
    }
}
