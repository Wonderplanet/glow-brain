<?php

namespace App\Constants;

enum GachaLogAggregationProgressStatus: string
{
    case IN_PROGRESS = 'InProgress'; // 進行中
    case COMPLETE = 'Complete'; // 完了
}
