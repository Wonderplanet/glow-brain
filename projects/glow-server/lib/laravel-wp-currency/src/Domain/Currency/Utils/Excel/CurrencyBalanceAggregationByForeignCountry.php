<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils\Excel;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class CurrencyBalanceAggregationByForeignCountry implements
    FromCollection,
    WithTitle,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting
{
    use Exportable;

    private string $year;
    private string $month;
    /**
     * @var Collection<int, mixed>
     */
    private Collection $summaryCollection;

    /**
     * レコードに追加する書式
     *
     * キーにはセルの範囲文字列を指定する
     * 値はPhpSpreadsheetのStyleの指定方法に依存するためmixedとしている
     *
     * @var array<string, mixed>
     */
    private array $summaryStyle = [];

    /**
     * @param Carbon $endAt
     * @param Collection<int, mixed> $summaryCollection
     */
    public function __construct(
        Carbon $endAt,
        Collection $summaryCollection
    ) {
        $this->year = $endAt->format('Y');
        $this->month = $endAt->format('m');
        $this->summaryCollection = $summaryCollection;
    }

    /**
     * @return Collection<int, mixed>
     */
    public function collection(): Collection
    {
        // 警告メッセージを取得
        $messages = $this->verify();
        $list[] = [$messages];

        // ヘッダ行生成
        $header = [
            '集計期間',
            '有償通貨販売個数',
            '有償通貨消費個数',
            '無効有償通貨',
            '有償通貨残個数(有効)',
            '為替コード',
            '為替レート(月末TTM)',
            '有償一次通貨残高(現地通貨換算金額)',
            '有償一次通貨残高',
        ];
        $list[] = $header;

        // 単位行生成
        $unit = ['単位', '個', '個', '個', '個', '', '￥', '通貨コードに伴う', '￥'];
        $list[] = $unit;

        if ($this->summaryCollection->isEmpty()) {
            $list[] = ['対象データが存在しません'];
            return collect($list);
        }

        // データ行生成(A列に"リリース〜"を埋め込んでいる)
        $rowCount = 4; // エクセル上の4行目からデータを書き込む
        $this->summaryCollection
            ->sortBy('currencyCode')
            ->map(function (array $row) use (&$list, &$rowCount) {
                $rateCalculatedRemainingAmountMoney = $row['rateCalculatedRemainingAmountMoney'];
                if ($rateCalculatedRemainingAmountMoney === '') {
                    // $rateCalculatedRemainingAmountMoneyが空文字ならExcelの数式を埋め込む
                    $rateCalculatedRemainingAmountMoney = "=G{$rowCount} * H{$rowCount}";
                }

                // rateが空白であれば背景色を黄色にする
                // Excelを作るときにソートされているので、ここでStyleを設定する
                if (blank($row['rate'])) {
                    $this->summaryStyle["A{$rowCount}:I{$rowCount}"] = [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => [
                                'argb' => Color::COLOR_YELLOW,
                            ],
                        ],
                    ];
                }

                $list[] = [
                    "リリース〜{$this->year}-{$this->month}",
                    $row['soldAmountByPaid'],
                    $row['consumeAmountByPaid'],
                    $row['invalidPaidAmount'],
                    $row['remainingAmountByPaid'],
                    $row['currencyCode'],
                    $row['rate'],
                    $row['remainingAmountMoney'],
                    $rateCalculatedRemainingAmountMoney,
                ];
                $rowCount++;
            });

        return collect($list);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return '海外';
    }

    /**
     * @param Worksheet $sheet
     * @return array<string, array<string, array<string, array<string, string>|string>>>
     */
    public function styles(Worksheet $sheet): array
    {
        return array_merge([
            // 警告行
            'A1' => ['font' => ['color' => ['argb' => Color::COLOR_RED]]],

            // ヘッダ
            'A2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_MAGENTA]]],
            'A3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_MAGENTA]]],
            'B2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'B3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'C2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'C3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'D2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'D3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'E2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'E3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'F2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'F3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'G2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'G3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_CYAN]]],
            'H2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
            'H3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
            'I2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_DARKYELLOW]]],
            'I3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_DARKYELLOW]]],
        ], $this->summaryStyle);
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'B' => CurrencyConstants::FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL,
            'C' => CurrencyConstants::FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL,
            'D' => CurrencyConstants::FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL,
            'E' => CurrencyConstants::FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    /**
     * 登録データの状態を確認する
     *
     * - currency_rateが空白の場合は警告メッセージを返す
     *
     * @return string
     */
    private function verify(): string
    {
        $messages = '';
        $emptyCurrencyRates = [];

        foreach ($this->summaryCollection as $i => $row) {
            if (blank($row['rate'])) {
                $emptyCurrencyRates[] = $row['currencyCode'];
            }
        }

        if (count($emptyCurrencyRates) > 0) {
            $messages .= "通貨レートが空白のデータがあります。";
            $currencyList = array_unique($emptyCurrencyRates);
            sort($currencyList);
            $messages .= "  対象通貨: " . implode(', ', $currencyList);
        }

        return $messages;
    }
}
