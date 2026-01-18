<?php

declare(strict_types=1);

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property int $date
 * @property int $status
 * @property bool $is_transfer
 * @property int $try_count
 */
class AdmDatalakeLog extends AdmModel
{
    use HasUuids;

    protected $table = 'adm_datalake_logs';

    protected $casts = [
        'is_transfer' => 'bool',
    ];


    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setIsTransfer(bool $isTransfer): void
    {
        $this->is_transfer = $isTransfer;
    }

    public function getIsTransfer(): bool
    {
        return $this->is_transfer;
    }

    public function setTryCount(int $tryCount): void
    {
        $this->try_count = $tryCount;
    }

    public function getTryCount(): int
    {
        return $this->try_count;
    }
}
