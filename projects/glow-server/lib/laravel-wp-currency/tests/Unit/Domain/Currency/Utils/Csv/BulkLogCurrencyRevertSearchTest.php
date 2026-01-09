<?php

declare(strict_types=1);

namespace Unit\Domain\Currency\Utils\Csv;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Utils\Csv\BulkLogCurrencyRevertSearch;

class BulkLogCurrencyRevertSearchTest extends TestCase
{
    #[Test]
    #[DataProvider('collectionSandboxData')]
    public function collection_出力データチェック(
        bool $isIncludeSandbox,
        string $expectedFileName
    ): void {
        // Setup
        $triggerType = 'Gacha';
        $triggerId = 'gacha01';
        $triggerName = 'ガチャ1';
        $startAt = new CarbonImmutable('2023-01-01 00:00:00', 'Asia/Tokyo');
        $endAt = new CarbonImmutable('2023-01-31 23:59:59', 'Asia/Tokyo');
        $collection = collect([
            $this->makeCollectItem(
                '1',
                '2023-01-01 00:00:00',
                $triggerType,
                $triggerId,
                $triggerName,
                'request1',
                100,
                0,
                '1',
                ''
            ),
            $this->makeCollectItem(
                '2',
                '2023-01-02 00:00:00',
                $triggerType,
                $triggerId,
                $triggerName,
                'request2',
                100,
                10,
                '2',
                '3'
            ),
            $this->makeCollectItem(
                '3',
                '2023-01-31 23:59:59',
                $triggerType,
                $triggerId,
                $triggerName,
                'request3',
                0,
                10,
                '',
                '4'
            ),
        ]);

        $bulkLogCurrencyRevertSearch = new BulkLogCurrencyRevertSearch(
            $collection,
            $startAt,
            $endAt,
            $isIncludeSandbox
        );

        // Exercise
        $excelDataArray = $bulkLogCurrencyRevertSearch
            ->collection()
            ->toArray();

        // Verify
        $this->assertCount(4, $excelDataArray);

        $headerRow = $excelDataArray[0];
        $this->assertEquals('ユーザーID', $headerRow[0]);
        $this->assertEquals('コンテンツ消費日時', $headerRow[1]);
        $this->assertEquals('消費コンテンツタイプ', $headerRow[2]);
        $this->assertEquals('消費コンテンツID', $headerRow[3]);
        $this->assertEquals('消費コンテンツ名', $headerRow[4]);
        $this->assertEquals('リクエストID', $headerRow[5]);
        $this->assertEquals('消費有償一次通貨数(合計)', $headerRow[6]);
        $this->assertEquals('消費無償一次通貨数(合計)', $headerRow[7]);
        $this->assertEquals('有償一次通貨の消費ログID', $headerRow[8]);
        $this->assertEquals('無償一次通貨の消費ログID', $headerRow[9]);

        $excelDataCollection = collect($excelDataArray);
        $row1 = $excelDataCollection->first(fn($row) => $row[0] === '1');
        $this->assertEquals('1', $row1[0]);
        $this->assertEquals('2023-01-01 00:00:00', $row1[1]);
        $this->assertEquals($triggerType, $row1[2]);
        $this->assertEquals($triggerId, $row1[3]);
        $this->assertEquals($triggerName, $row1[4]);
        $this->assertEquals('request1', $row1[5]);
        $this->assertEquals('100', $row1[6]);
        $this->assertEquals('0', $row1[7]);
        $this->assertEquals('1', $row1[8]);
        $this->assertEmpty($row1[9]);

        $row2 = $excelDataCollection->first(fn($row) => $row[0] === '2');
        $this->assertEquals('2', $row2[0]);
        $this->assertEquals('2023-01-02 00:00:00', $row2[1]);
        $this->assertEquals($triggerType, $row2[2]);
        $this->assertEquals($triggerId, $row2[3]);
        $this->assertEquals($triggerName, $row2[4]);
        $this->assertEquals('request2', $row2[5]);
        $this->assertEquals('100', $row2[6]);
        $this->assertEquals('10', $row2[7]);
        $this->assertEquals('2', $row2[8]);
        $this->assertEquals('3', $row2[9]);

        $row3 = $excelDataCollection->first(fn($row) => $row[0] === '3');
        $this->assertEquals('3', $row3[0]);
        $this->assertEquals('2023-01-31 23:59:59', $row3[1]);
        $this->assertEquals($triggerType, $row3[2]);
        $this->assertEquals($triggerId, $row3[3]);
        $this->assertEquals($triggerName, $row3[4]);
        $this->assertEquals('request3', $row3[5]);
        $this->assertEquals('0', $row3[6]);
        $this->assertEquals('10', $row3[7]);
        $this->assertEmpty($row3[8]);
        $this->assertEquals('4', $row3[9]);

        // ファイル名が指定した期間から付けられている
        $this->assertEquals(
            $expectedFileName,
            $bulkLogCurrencyRevertSearch->getFileName(),
        );
    }

    /**
     * @return array[]
     */
    public static function collectionSandboxData(): array
    {
        return [
            'サンドボックスデータを含めない' => [
                false,
                '一次通貨返却対象データレポート_20230101000000-20230131235959.csv'
            ],
            'サンドボックスデータを含める' => [
                true,
                '一次通貨返却対象データレポート_20230101000000-20230131235959_サンドボックスデータ含む.csv'
            ],
        ];
    }

    private function makeCollectItem(
        string $usrUserId,
        string $createdAt,
        string $triggerType,
        string $triggerId,
        string $triggerName,
        string $requestId,
        int $sumLogChangeAmountPaid,
        int $sumLogChangeAmountFree,
        string $logCurrencyPaidIds,
        string $logCurrencyFreeIds,
    ): array {
        return [
            'usr_user_id' => $usrUserId,
            'consumed_at' => $createdAt,
            'trigger_type' => $triggerType,
            'trigger_id' => $triggerId,
            'trigger_name' => $triggerName,
            'request_id' => $requestId,
            'sum_log_change_amount_paid' => $sumLogChangeAmountPaid,
            'sum_log_change_amount_free' => $sumLogChangeAmountFree,
            'log_currency_paid_ids' => $logCurrencyPaidIds,
            'log_currency_free_ids' => $logCurrencyFreeIds,
        ];
    }
}
