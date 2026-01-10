<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Models\Adm;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;

class AdmBulkCurrencyRevertTaskTargetTest extends TestCase
{
    public static function paidLogsDataProvider(): array
    {
        return [
            'ログ通貨IDが空文字の場合' => ['', []],
            'ログ通貨IDが1つの場合' => ['1', ['1']],
            'ログ通貨IDが複数の場合' => ['1,2,3', ['1', '2', '3']],
        ];
    }

    #[Test]
    #[DataProvider('paidLogsDataProvider')]
    public function paidLogs_ログ通貨IDの配列を取得する($ids, $expected)
    {
        // Setup
        $expandIds = ($ids !== '') ? explode(',', $ids) : [];
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create();
        $target->paidLogs()->createMany(array_map(function ($id) {
            return [
                'log_currency_paid_id' => $id,
                'usr_user_id' => 'user-1',
            ];
        }, $expandIds));

        // Exercise
        $actual = $target->paidLogs()->pluck('log_currency_paid_id')->sort()->values()->toArray();

        // Verify
        $this->assertSame($expected, $actual);
    }

    public static function freeLogsDataProvider(): array
    {
        return [
            'ログ通貨IDが空文字の場合' => ['', []],
            'ログ通貨IDが1つの場合' => ['1', ['1']],
            'ログ通貨IDが複数の場合' => ['1,2,3', ['1', '2', '3']],
        ];
    }

    #[Test]
    #[DataProvider('freeLogsDataProvider')]
    public function freeLogs_ログ通貨IDの配列を取得する($ids, $expected)
    {
        // Setup
        $expandIds = ($ids !== '') ? explode(',', $ids) : [];
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create();
        $target->freeLogs()->createMany(array_map(function ($id) {
            return [
                'log_currency_free_id' => $id,
                'usr_user_id' => 'user-1',
            ];
        }, $expandIds));

        // Exercise
        $actual = $target->freeLogs()->pluck('log_currency_free_id')->sort()->values()->toArray();

        // Verify
        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function revertHistoryLogs_ログ通貨IDを取得する()
    {
        // Setup
        $target = AdmBulkCurrencyRevertTaskTarget::factory()->create();
        $target->revertHistoryLogs()->create([
            'log_currency_revert_history_id' => '1',
            'usr_user_id' => 'user-1',
        ]);

        // Exercise
        $actual = $target->revertHistoryLogs()->pluck('log_currency_revert_history_id')->toArray();

        // Verify
        $this->assertSame(['1'], $actual);
    }
}
