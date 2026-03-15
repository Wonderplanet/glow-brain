<?php

declare(strict_types=1);

namespace Tests\Support\Traits;

use App\Domain\Resource\Log\Models\LogBank;

trait TestBankKpiTrait
{
    /**
     * log_bankテーブルにリクエスト情報が保存されていることを確認
     */
    protected function checkBankLogByEventId(
        string $usrUserId,
        int $eventId,
    ): void {
        $logBank = LogBank::query()
            ->where('usr_user_id', $usrUserId)
            ->orderBy('created_at', 'desc')
            ->first();
        $this->assertNotNull($logBank);
        $this->assertEquals($eventId, $logBank->event_id);
    }
}
