<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Enums;

/**
 * 一次通貨返却タスクのステータス
 */
enum AdmBulkCurrencyRevertTaskStatus: string
{
    case Ready = 'Ready'; // 開始前
    case RegisterProcessing = 'RegisterProcessing'; // 登録中
    case Registered = 'Registered'; // 登録済み
    case Processing = 'Processing'; // 処理中
    case Finished = 'Finished'; // 完了
    case Error = 'Error'; // エラー
}
