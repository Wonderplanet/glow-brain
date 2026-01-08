<?php

declare(strict_types=1);

namespace App\Domain\Party\Models\Eloquent;

use App\Domain\Resource\Usr\Models\UsrEloquentModel;

/**
 * @property string $id
 * @property string $usr_user_id
 * @property int $party_no
 * @property string $party_name
 * @property string $usr_unit_id_1
 * @property string $usr_unit_id_2
 * @property string $usr_unit_id_3
 * @property string $usr_unit_id_4
 * @property string $usr_unit_id_5
 * @property string $usr_unit_id_6
 * @property string $usr_unit_id_7
 * @property string $usr_unit_id_8
 * @property string $usr_unit_id_9
 * @property string $usr_unit_id_10
 */
class UsrParty extends UsrEloquentModel
{
    /**
     * getUsrUnitId{数値}メソッドが呼ばれたら、usr_unit_id_{数値}を返す
     */
    public function __call($method, $parameters)
    {
        if (preg_match('/^getUsrUnitId(\d+)$/', $method, $matches)) {
            $index = (int)$matches[1];
            return $this->attributes["usr_unit_id_$index"];
        }
        return parent::__call($method, $parameters);
    }
}
