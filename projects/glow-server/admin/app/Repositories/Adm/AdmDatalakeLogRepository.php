<?php

namespace App\Repositories\Adm;

use App\Constants\DatalakeStatus;
use App\Models\Adm\AdmDatalakeLog;

class AdmDatalakeLogRepository
{
    /**
     * @param int $date
     * @return AdmDatalakeLog|null
     */
    public function getByDate(int $date): ?AdmDatalakeLog
    {
        return AdmDatalakeLog::query()
            ->where('date', $date)
            ->first();
    }

    /**
     * @param int $date
     * @return AdmDatalakeLog
     */
    public function createModel(int $date): AdmDatalakeLog
    {
        $model = new AdmDatalakeLog();
        $model->setDate($date);
        $model->setStatus(DatalakeStatus::NOT_STARTED->value);
        $model->setIsTransfer(false);
        $model->setTryCount(0);
        return $model;
    }
}
