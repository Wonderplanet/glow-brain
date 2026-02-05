<?php

namespace App\Models\Mng;

use App\Constants\Database;
use App\Domain\Resource\Mng\Models\MngContentClose as BaseMngContentClose;
use Carbon\CarbonImmutable;

class MngContentClose extends BaseMngContentClose
{
    protected $connection = Database::MANAGE_DATA_CONNECTION;

    public function calcStatus(CarbonImmutable $now): string
    {
        if (!$this->is_valid) {
            return '無効';
        }
        
        if ($now < $this->start_at) {
            return 'クローズ前';
        } elseif ($now > $this->end_at) {
            return 'クローズ終了';
        } else {
            return 'クローズ中';
        }
    }

    public function calcStatusBadgeColor(CarbonImmutable $now): string
    {
        if (!$this->is_valid) {
            return 'gray';
        }
        
        $status = $this->calcStatus($now);

        switch ($status) {
            case 'クローズ前':
                return 'primary';
            case 'クローズ中':
                return 'success';
            case 'クローズ終了':
                return 'info';
            default:
                return 'gray';
        }
    }
}
