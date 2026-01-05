<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Enums;

/**
 * 一次通貨返却タスクのステータス
 */
enum AdmBulkCurrencyRevertTaskTargetStatus: string
{
    case Ready = 'Ready'; // 開始前
    case Processing = 'Processing'; // 処理中
    case Finished = 'Finished'; // 完了
    case Error = 'Error'; // エラー
}
