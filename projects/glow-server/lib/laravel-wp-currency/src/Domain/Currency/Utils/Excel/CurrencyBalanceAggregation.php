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

class CurrencyBalanceAggregation implements FromCollection, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    private string $year;
    private string $month;
    /**
     * @var Collection<string, mixed>
     */
    private Collection $summaryCollection;
    private ?string $billingPlatform;

    /**
     * @param Carbon $endAt
     * @param Collection<string, mixed> $summaryCollection
     * @param string|null $billingPlatform
     */
    public function __construct(
        Carbon $endAt,
        Collection $summaryCollection,
        ?string $billingPlatform
    ) {
        $this->year = $endAt->format('Y');
        $this->month = $endAt->format('m');
        $this->summaryCollection = $summaryCollection;
        $this->billingPlatform = $billingPlatform;
    }

    /**
     * @return Collection<int, mixed>
     */
    public function collection(): Collection
    {
        // 警告メッセージ行を追加
        // 日本向けサマリーでは発生しないがフォーマットを合わせるために追加
        $messages = '';
        $list[] = [$messages];

        // ヘッダ行生成
        $header = [
            '集計期間',
            '有償通貨販売個数',
            '有償通貨消費個数',
            '無効有償通貨',
            '有償通貨残個数(有効)',
            '有償通貨販売金額',
            '有償通貨消費金額',
            '有償一次通貨残高',
        ];
        $list[] = $header;

        // 単位行生成
        $unit = ['単位', '個', '個', '個', '個', '￥', '￥', '￥'];
        $list[] = $unit;

        if ($this->summaryCollection->count() === 0) {
            $list[] = ['対象データが存在しません'];
            return collect($list);
        }

        // データ行生成(A:3に"リリース〜"を埋め込んでいる)
        $row = [
            "リリース〜{$this->year}-{$this->month}",
            $this->summaryCollection->get('soldAmountByPaid'),
            $this->summaryCollection->get('consumeAmountByPaid'),
            $this->summaryCollection->get('invalidPaidAmount'),
            $this->summaryCollection->get('remainingAmountByPaid'),
            $this->summaryCollection->get('soldAmountMoney'),
            $this->summaryCollection->get('consumeAmountMoney'),
            $this->summaryCollection->get('remainingAmountMoney'),
        ];
        $list[] = $row;

        return collect($list);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return match ($this->billingPlatform) {
            CurrencyConstants::PLATFORM_APPSTORE => '日本Apple(サマリー)',
            CurrencyConstants::PLATFORM_GOOGLEPLAY => '日本Google(サマリー)',
            default => '日本累計(サマリー)',
        };
    }

    /**
     * @param Worksheet $sheet
     * @return array<string, array<string, array<string, array<string, string>|string>>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
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
            'F2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
            'F3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
            'G2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
            'G3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_YELLOW]]],
            'H2' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_DARKYELLOW]]],
            'H3' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => Color::COLOR_DARKYELLOW]]],
        ];
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
        ];
    }
}
