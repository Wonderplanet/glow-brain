<?php

namespace App\Models\Usr;

use App\Constants\Database;
use App\Domain\Message\Models\UsrTemporaryIndividualMessage as BaseUsrTemporaryIndividualMessage;

class UsrTemporaryIndividualMessage extends BaseUsrTemporaryIndividualMessage
{
    protected $connection = Database::TIDB_CONNECTION;

    /**
     * Factoryクラスの取得 (デフォルトに戻す)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
    }
}
