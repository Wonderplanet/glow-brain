<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 有償一次通貨の返却を行なった際のログと通貨ログの紐付け用クラス
 * 返却した対象のログとlog_currency_revert_historiesの紐付けを行うためのテーブルを追加
 *
 * このテーブルは紐付けのためのデータなので、triggerなどは格納していない
 *
 * @property string $id
 * @property string $usr_user_id
 * @property string $log_currency_revert_history_id
 * @property string $log_currency_paid_id この返却操作で記録されたログ
 * @property string $revert_log_currency_paid_id 返却操作の対象になった、返却個数が記録されたログ
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LogCurrencyRevertHistoryPaidLog extends BaseLogModel
{
}
