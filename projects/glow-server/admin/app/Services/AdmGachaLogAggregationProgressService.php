<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\GachaLogAggregationProgressStatus;
use App\Models\Adm\AdmGachaLogAggregationProgress;

class AdmGachaLogAggregationProgressService
{
    private function getByTargetDate(string $targetDate): ?AdmGachaLogAggregationProgress
    {
        return AdmGachaLogAggregationProgress::query()->where('target_date', $targetDate)->first();
    }

    /**
     * AdmGachaLogAggregationProgressモデルを作成する
     *
     * @param string $targetDate
     * @return AdmGachaLogAggregationProgress
     */
    public function getOrCreate(string $targetDate): AdmGachaLogAggregationProgress
    {
        $admGachaLogAggregationProgress = $this->getByTargetDate($targetDate);
        if (!is_null($admGachaLogAggregationProgress)) {
            return $admGachaLogAggregationProgress;
        }
        $admGachaLogAggregationProgress = new AdmGachaLogAggregationProgress();

        $admGachaLogAggregationProgress->id = $admGachaLogAggregationProgress->newUniqueId();
        $admGachaLogAggregationProgress->status = GachaLogAggregationProgressStatus::IN_PROGRESS->value;
        $admGachaLogAggregationProgress->progress = 0;
        $admGachaLogAggregationProgress->target_date = $targetDate;

        $admGachaLogAggregationProgress->save();

        return $admGachaLogAggregationProgress;
    }
}
