<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;

/**
 * 一次通貨返却タスクの対象ユーザー情報
 *
 * @property string $id
 * @property string $adm_bulk_currency_revert_task_id
 * @property int $seq_no
 * @property string $usr_user_id
 * @property AdmBulkCurrencyRevertTaskTargetStatus $status
 * @property int $revert_currency_num
 * @property \Carbon\CarbonImmutable $consumed_at
 * @property string $trigger_type
 * @property string $trigger_id
 * @property string $trigger_name
 * @property string $request_id
 * @property int $sum_log_change_amount_paid
 * @property int $sum_log_change_amount_free
 * @property string $comment
 * @property string $error_message
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class AdmBulkCurrencyRevertTaskTarget extends BaseAdminModel
{
    protected $fillable = [
        'adm_bulk_currency_revert_task_id',
        'seq_no',
        'usr_user_id',
        'status',
        'revert_currency_num',
        'consumed_at',
        'trigger_type',
        'trigger_id',
        'trigger_name',
        'request_id',
        'sum_log_change_amount_paid',
        'sum_log_change_amount_free',
        'log_currency_paid_ids',
        'log_currency_free_ids',
        'comment',
        'error_message',
        'log_currency_revert_history_id',
    ];

    protected $casts = [
        'status' => AdmBulkCurrencyRevertTaskTargetStatus::class,
        'consumed_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * タスクが開始前かどうか
     *
     * @return boolean
     */
    public function isReady(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskTargetStatus::Ready;
    }

    /**
     * タスクが処理中かどうか
     */
    public function isProcessing(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskTargetStatus::Processing;
    }

    /**
     * タスクが完了しているかどうか
     *
     * @return boolean
     */
    public function isFinished(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskTargetStatus::Finished;
    }

    /**
     * タスクがエラーかどうか
     *
     * @return boolean
     */
    public function isError(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskTargetStatus::Error;
    }

    /**
     * 返却対象の有償一次通貨ログID
     *
     * @return HasMany<AdmBulkCurrencyRevertTaskTargetPaidLog, $this>
     */
    public function paidLogs(): HasMany
    {
        return $this->hasMany(AdmBulkCurrencyRevertTaskTargetPaidLog::class);
    }

    /**
     * 返却対象の無償一次通貨ログID
     *
     * @return HasMany<AdmBulkCurrencyRevertTaskTargetFreeLog, $this>
     */
    public function freeLogs(): HasMany
    {
        return $this->hasMany(AdmBulkCurrencyRevertTaskTargetFreeLog::class);
    }

    /**
     * 返却後の一次通貨返却ログID
     *
     * @return HasMany<AdmBulkCurrencyRevertTaskTargetRevertHistoryLog, $this>
     */
    public function revertHistoryLogs(): HasMany
    {
        return $this->hasMany(AdmBulkCurrencyRevertTaskTargetRevertHistoryLog::class);
    }
}
