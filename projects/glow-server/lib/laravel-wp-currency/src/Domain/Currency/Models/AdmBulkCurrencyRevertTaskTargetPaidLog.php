<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 一次通貨返却タスクの対象となるlog_currency_paid_idのテーブル
 *
 * @param string $id
 * @param string $adm_bulk_currency_revert_task_target_id
 * @param string $usr_user_id
 * @param string $log_currency_paid_id
 * @param \Carbon\CarbonImmutable $created_at
 * @param \Carbon\CarbonImmutable $updated_at
 */
class AdmBulkCurrencyRevertTaskTargetPaidLog extends BaseAdminModel
{
    protected $fillable = [
        'adm_bulk_currency_revert_task_target_id',
        'usr_user_id',
        'log_currency_paid_id',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
