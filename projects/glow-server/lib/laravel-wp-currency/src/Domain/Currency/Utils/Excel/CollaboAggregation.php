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

/**
 * コラボ消費通貨集計結果Excel出力
 */
class CollaboAggregation implements FromCollection, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    /**
     * 出力するファイル名
     *
     * Exportableトレイト内で参照する
     *
     * @var string
     */
    private string $fileName;

    /**
     * 出力するデータ
     *
     * @var Collection<int, mixed>
     */
    private Collection $dataCollection;

    /**
     * 検索条件に指定したトリガーID
     *
     * @var array<array{type: string, ids: array<string>}>
     */
    private array $searchTriggers;

    /**
     * dataレコードのスタイル
     *
     * キーにはセルの範囲文字列を指定する
     * 値はPhpSpreadsheetのStyleの指定方法に依存するためmixedとしている
     *
     * @var array<string, mixed>
     */
    private array $dataStyle = [];

    /**
     * コンストラクタ
     *
     * 検索条件のトリガーIDに一致しない場合の処理のため、コンストラクタで受け取る
     *
     * @param Collection<int, mixed> $dataCollection
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @param array<array{type: string, ids: array<string>}> $searchTriggers
     * @param bool $isIncludeSandbox
     */
    public function __construct(
        Collection $dataCollection,
        Carbon $startAt,
        Carbon $endAt,
        array $searchTriggers,
        bool $isIncludeSandbox
    ) {
        $this->dataCollection = $dataCollection;
        $this->searchTriggers = $searchTriggers;

        // 開始日と終了日からファイル名を生成
        $startTime = $startAt->clone()->setTimezone(CurrencyConstants::OUTPUT_TZ)->format('YmdHis');
        $endTime = $endAt->clone()->setTimezone(CurrencyConstants::OUTPUT_TZ)->format('YmdHis');

        $this->fileName = 'コラボ消費通貨集計レポート_' . $startTime . '-' . $endTime;
        if ($isIncludeSandbox) {
            $this->fileName .= '_' . 'サンドボックスデータ含む';
        }
        $this->fileName .= '.xlsx';
    }

    /**
     * 設定されているファイル名を参照するためのgetter
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
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
            'gacha_id/product_id',
            'currency',
            '消費年月',
            '有償通貨単価',
            '月末TTM',
            '消費有償通貨数',
            '消費有償通貨額（円）',
        ];
        $list[] = $header;

        if ($this->dataCollection->count() === 0) {
            $list[] = ['対象データが存在しません'];
            return collect($list);
        }

        $rowCount = 3; // エクセル上の3行目から開始
        $this->dataCollection
            ->map(function (array $row) use (&$list, &$rowCount) {
                $rateCalculatedMoney = $row['rate_calculated_money'];

                // rate_calculated_moneyが空文字の場合、手動で入力してもらう想定でExcel計算式で初期化
                if ($rateCalculatedMoney === '') {
                    $rateCalculatedMoney = "=C{$rowCount} * D{$rowCount} * E{$rowCount}";
                }
                // ttmが空白であれば背景色を黄色にする
                if (blank($row['ttm'])) {
                    $this->dataStyle["A{$rowCount}:G{$rowCount}"] = [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => [
                                'argb' => Color::COLOR_YELLOW,
                            ],
                        ],
                    ];
                }

                $row = [
                    $row['trigger_id'],
                    $row['currency_code'],
                    $row['year_month_created_at'],
                    $row['price_per_amount'],
                    $row['ttm'],
                    $row['sum_amount'],
                    $rateCalculatedMoney,
                ];
                $list[] = $row;
                $rowCount++;
            });

        // $searchTriggersにあってdataCollectionにないものを追加
        //   rowはID列以外-で埋める
        $noEntryList = [];
        foreach ($this->searchTriggers as $searchTrigger) {
            $type = $searchTrigger['type'];
            $ids = $searchTrigger['ids'];
            foreach ($ids as $id) {
                // dataCollectionにあるかチェック
                if ($this->dataCollection->where('trigger_type', $type)->where('trigger_id', $id)->count() > 0) {
                    continue;
                }

                // dataCollectionにないので追加
                $row = [
                    $id,
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                ];
                $noEntryList[] = $row;
            }
        }
        // noEntryListをid順にソート
        $noEntryList = collect($noEntryList)->sortBy(function ($row) {
            return $row[0];
        })->toArray();
        $list = array_merge($list, $noEntryList);

        return collect($list);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'コラボ通貨消費集計';
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            // 有償通貨単価
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            // 月末TTM
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            // 消費有償通貨数
            'F' => CurrencyConstants::FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL,
            // 消費有償通貨額（円）
            'G' => CurrencyConstants::FORMAT_NUMBER_COMMA_SEPARATED_ORIGINAL,
        ];
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
        ], $this->dataStyle);
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

        foreach ($this->dataCollection as $row) {
            if (blank($row['ttm'])) {
                $emptyCurrencyRates[] = $row['currency_code'];
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
