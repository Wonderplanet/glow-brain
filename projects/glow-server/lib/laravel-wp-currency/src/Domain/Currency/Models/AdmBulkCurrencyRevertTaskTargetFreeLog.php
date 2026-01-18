<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

/**
 * 一次通貨返却タスクの対象となるlog_currency_free_idのテーブル
 *
 * @param string $id
 * @param string $adm_bulk_currency_revert_task_target_ids
 * @param string $usr_user_id
 * @param string $log_currency_free_id
 * @param \Carbon\CarbonImmutable $created_at
 * @param \Carbon\CarbonImmutable $updated_at
 */
class AdmBulkCurrencyRevertTaskTargetFreeLog extends BaseAdminModel
{
    protected $fillable = [
        'adm_bulk_currency_revert_task_target_ids',
        'usr_user_id',
        'log_currency_free_id',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
