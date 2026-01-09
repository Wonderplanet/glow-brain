<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Resource\Sys\Entities\SysPvpSeasonEntity;

/**
 * ログイン時に必要なPVP情報をまとめたデータクラス
 */
class PvpLoginData
{
    public function __construct(
        private SysPvpSeasonEntity $sysPvpSeasonEntity,
        private UsrPvpStatusData $usrPvpStatusData,
    ) {
    }

    public function  getSysPvpSeasonEntity(): SysPvpSeasonEntity
    {
        return $this->sysPvpSeasonEntity;
    }

    public function getUsrPvpStatusData(): UsrPvpStatusData
    {
        return $this->usrPvpStatusData;
    }
}
