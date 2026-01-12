<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Utils\Excel;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Utils\Excel\BillingLogReport;

class BillingLogReportTest extends TestCase
{
    #[Test]
    #[DataProvider('writerData')]
    public function writer_チェック(Collection $data, int $expectedCount): void
    {
        // Setup
        $billingLogReport = new BillingLogReport('2023', '12', false, $data);

        // Exercise
        $writer = $billingLogReport->writer();

        // Verify
        //  実際に書き込まれたデータを参照するのは難しいので、シートに書き込まれた行数をチェックする
        $this->assertEquals($expectedCount, $writer->countSheetRows('課金ログレポート_2023-12'));
    }

    /**
     * @return array[]
     */
    public static function writerData(): array
    {
        return [
            '書き込みデータがある' => [
                collect([
                    [
                        'player_id' => 'player_id_1',
                        'market' => 'aapl',
                        'order_id' => 'order_id_1',
                        'product_id' => 'product_id_1',
                        'currency' => 'JPY',
                        'price' => '100.00000000',
                        'currency_rate' => '1',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                    [
                        'player_id' => 'player_id_2',
                        'market' => 'goog',
                        'order_id' => 'order_id_2',
                        'product_id' => 'product_id_2',
                        'currency' => 'USD',
                        'price' => '100.10000000',
                        'currency_rate' => '143.27',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                ]),
                4, // 警告メッセージ行 + ヘッダー行 + レコード行数
            ],
            '書き込みデータが空' => [
                collect([]), // レコードは空
                3, // 警告メッセージ行 +  ヘッダー行 + 「対象データが存在しません」の行数
            ],
        ];
    }


    public static function verifyData(): array
    {
        return [
            '通貨レードが全て記載' => [
                collect([
                    [
                        'player_id' => 'player_id_1',
                        'market' => 'aapl',
                        'order_id' => 'order_id_1',
                        'product_id' => 'product_id_1',
                        'currency' => 'JPY',
                        'price' => '100.00000000',
                        'currency_rate' => '1',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                    [
                        'player_id' => 'player_id_2',
                        'market' => 'goog',
                        'order_id' => 'order_id_2',
                        'product_id' => 'product_id_2',
                        'currency' => 'USD',
                        'price' => '100.10000000',
                        'currency_rate' => '143.27',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                ]),
                '', // 警告文なし
                [], // オプションもなし
            ],
            '通貨レートが抜けている' => [
                collect([
                    [
                        'player_id' => 'player_id_1',
                        'market' => 'aapl',
                        'order_id' => 'order_id_1',
                        'product_id' => 'product_id_1',
                        'currency' => 'JPY',
                        'price' => '100.00000000',
                        'currency_rate' => '1',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                    [
                        'player_id' => 'player_id_2',
                        'market' => 'goog',
                        'order_id' => 'order_id_2',
                        'product_id' => 'product_id_2',
                        'currency' => 'USD',
                        'price' => '100.10000000',
                        'currency_rate' => '',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                    [
                        'player_id' => 'player_id_2',
                        'market' => 'goog',
                        'order_id' => 'order_id_2',
                        'product_id' => 'product_id_2',
                        'currency' => 'EUR',
                        'price' => '100.10000000',
                        'currency_rate' => '',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                    [
                        'player_id' => 'player_id_2',
                        'market' => 'goog',
                        'order_id' => 'order_id_2',
                        'product_id' => 'product_id_2',
                        'currency' => 'JPY',
                        'price' => '100.10000000',
                        'currency_rate' => '1',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                    [
                        'player_id' => 'player_id_2',
                        'market' => 'goog',
                        'order_id' => 'order_id_2',
                        'product_id' => 'product_id_2',
                        'currency' => 'EUR',
                        'price' => '100.10000000',
                        'currency_rate' => '',
                        'formatted_created_at' => '2023/12/01 00:00:00',
                    ],
                ]),
                "通貨レートが空白のデータがあります。  対象通貨: EUR, USD", // 警告文
                [
                    1 => ['fill' => '#FFFF00'],
                    2 => ['fill' => '#FFFF00'],
                    4 => ['fill' => '#FFFF00'],
                ], // オプション
            ],
        ];
    }

    #[Test]
    #[DataProvider('verifyData')]
    public function verify_通貨レートが抜けている場合の警告文(
        Collection $data,
        string $expectedMessage,
        array $expectedDataOptions
    ): void {
        // Setup
        $billingLogReport = new BillingLogReport('2023', '12', false, $data);

        // Exercise
        $message = $this->callMethod($billingLogReport, 'verify');

        // Verify
        $dataOptions = $this->getProperty($billingLogReport, 'dataOptions');
        $this->assertEquals($expectedMessage, $message);
        $this->assertEquals($expectedDataOptions, $dataOptions);
    }
}
