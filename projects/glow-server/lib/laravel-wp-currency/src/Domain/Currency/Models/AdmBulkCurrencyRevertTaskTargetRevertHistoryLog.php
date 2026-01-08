<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 一次通貨返却タスクの返却を実施した後の
 * log_currency_revert_history_idのテーブル
 *
 * @param string $id
 * @param string $adm_bulk_currency_revert_task_target_id
 * @param string $usr_user_id
 * @param string $log_currency_revert_history_id
 * @param \Carbon\CarbonImmutable $created_at
 * @param \Carbon\CarbonImmutable $updated_at
 */
class AdmBulkCurrencyRevertTaskTargetRevertHistoryLog extends BaseAdminModel
{
    protected $fillable = [
        'adm_bulk_currency_revert_task_target_id',
        'usr_user_id',
        'log_currency_revert_history_id',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
