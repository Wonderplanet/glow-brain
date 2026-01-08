<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskStatus;

/**
 * 一次通貨返却一括処理タスク
 *
 * @property string $id
 * @property int $adm_user_id
 * @property string $file_name
 * @property int $revert_currency_num
 * @property string $comment
 * @property AdmBulkCurrencyRevertTaskStatus $status
 * @property int $total_count
 * @property int $success_count
 * @property int $error_count
 * @property string $error_message
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class AdmBulkCurrencyRevertTask extends BaseAdminModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'adm_user_id',
        'file_name',
        'revert_currency_num',
        'comment',
        'status',
        'total_count',
        'success_count',
        'error_count',
        'error_message',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => AdmBulkCurrencyRevertTaskStatus::class,
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * 処理開始前かどうか
     *
     * @return boolean
     */
    public function isReady(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskStatus::Ready;
    }

    /**
     * タスクのユーザーデータを登録中かどうか
     *
     * @return boolean
     */
    public function isRegisterProcessing(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskStatus::RegisterProcessing;
    }

    /**
     * タスクのユーザーデータを登録済みかどうか
     *
     * @return boolean
     */
    public function isRegistered(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskStatus::Registered;
    }

    /**
     * タスクが処理中かどうか
     *
     * @return boolean
     */
    public function isProcessing(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskStatus::Processing;
    }

    /**
     * タスクが完了しているかどうか
     *
     * @return boolean
     */
    public function isFinished(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskStatus::Finished;
    }

    /**
     * エラーで中断されたかどうか
     *
     * @return boolean
     */
    public function isError(): bool
    {
        return $this->status === AdmBulkCurrencyRevertTaskStatus::Error;
    }
}
