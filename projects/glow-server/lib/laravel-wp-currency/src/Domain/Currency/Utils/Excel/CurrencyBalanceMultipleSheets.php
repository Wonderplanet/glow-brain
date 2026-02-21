<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils\Excel;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * 一次通貨残高集計レポートのエクセルファイルを生成するクラス
 *
 * 複数のシートを持つエクセルファイルを生成する
 */
class CurrencyBalanceMultipleSheets implements WithMultipleSheets
{
    use Exportable;

    /**
     * @var mixed[]
     */
    private array $sheets;

    /**
     * ファイル名
     *
     * @var string
     */
    private string $fileName;

    /**
     * @param string $year
     * @param string $month
     * @param mixed[] $exports
     * @param bool $isIncludeSandbox
     */
    public function __construct(
        string $year,
        string $month,
        array $exports,
        bool $isIncludeSandbox
    ) {
        $this->sheets = $exports;

        // ファイル名を設定する
        $this->fileName = '一次通貨残高集計レポート_' . $year . '-' . $month;
        if ($isIncludeSandbox) {
            $this->fileName .= '_' . 'サンドボックスデータ含む';
        }
        $this->fileName .= '.xlsx';
    }

    /**
     * @return mixed[]
     */
    public function sheets(): array
    {
        return $this->sheets;
    }

    /**
     * ファイル名を取得する
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }
}
