<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Utils\Excel;

use Illuminate\Support\Collection;
use XLSXWriter;

class BillingLogReport
{
    private const HEADER = [
        'player_id' => 'string',
        'market' => 'string',
        'order_id' => 'string',
        'product_id' => 'string',
        'currency' => 'string',
        'price' => 'price',
        'currency_rate' => 'string',
        'created_at' => 'string',
    ];

    private string $sheetName;
    private string $fileName;
    /**
     * @var Collection<int, mixed>
     */
    private Collection $data;

    /**
     * dataに対するstyleなどのオプションを指定する
     *
     * キーは行番号、値はXLSXWriter::writeSheetRowの第3引数に指定するオプションのためmixedにしている
     * @var array<int, mixed>
     */
    private array $dataOptions;

    /**
     * コンストラクタ
     *
     * dataについては、Eloquentモデルのget()->toArray()を格納している
     * そのためカラム名がキーとなっている
     *
     * @param string $year
     * @param string $month
     * @param bool $isIncludeSandbox
     * @param Collection<int, mixed> $data
     */
    public function __construct(string $year, string $month, bool $isIncludeSandbox, Collection $data)
    {
        $this->sheetName = '課金ログレポート_' . $year . '-' . $month;
        $this->fileName = $this->sheetName;
        if ($isIncludeSandbox) {
            $this->fileName .= '_' . 'サンドボックスデータ含む';
        }
        $this->fileName .= '.xlsx';

        $this->data = $data;
        $this->dataOptions = [];
    }

    /**
     * 登録データの状態を確認する
     *
     * - currency_rateが空白の場合は警告を追加する
     * - 空白行の背景を黄色にする
     *
     * @return string 警告メッセージ
     */
    private function verify(): string
    {
        $messages = '';
        $emptyCurrencyRates = [];

        foreach ($this->data as $i => $row) {
            // currencyRateが空白の場合は警告を追加する
            if (blank($row['currency_rate'])) {
                // 空白のcurrencyRateを取得
                $emptyCurrencyRates[] = $row['currency'];
                // オプションに背景色黄色を追加
                //   XLSXWriter::writeSheetRow内で、セル単位のスタイルを指定するには
                //   すべてのセルのスタイルを指定する必要がある。
                //   ただ課金ログレポートはレコード数が多いため、それをするよりは行単位で塗りつぶすようにした。
                //   警告の意味としてはこれでも問題ないと思う
                $this->addRowStyle($i, ['fill' => '#FFFF00']);
            }
        }

        // 空白のcurrencyRateがある場合は警告を追加する
        if (count($emptyCurrencyRates) > 0) {
            $messages .= "通貨レートが空白のデータがあります。";
            // 対象のcurrencyを文字順にソートしてメッセージに追加する
            $currencyList = array_unique($emptyCurrencyRates);
            sort($currencyList);
            $messages .= "  対象通貨: " . implode(', ', $currencyList);
        }

        return $messages;
    }

    /**
     * @return XLSXWriter
     */
    public function writer(): XLSXWriter
    {
        $writer = new XLSXWriter();

        // 警告を取得し書き込む
        $messages = $this->verify();
        $writer->writeSheetRow($this->sheetName, [$messages], ['color' => '#FF0000']);

        // ヘッダー行書き込み実行
        $writer->writeSheetHeader($this->sheetName, self::HEADER);

        if ($this->data->isEmpty()) {
            // 取得データが空だった場合はメッセージだけ書き込む
            $writer->writeSheetRow($this->sheetName, ['対象データが存在しません']);
            return $writer;
        }

        // データ書き込み実行
        foreach ($this->data as $i => $row) {
            $writer->writeSheetRow($this->sheetName, $row, $this->dataOptions[$i] ?? null);
        }

        // 書き込んだファイル情報のオブジェクトを返す
        return $writer;
    }

    /**
     * ファイルネームを取得する
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * 行にスタイルを追加する
     *
     * @param integer $rowIndex
     * @param array<mixed> $style
     * @return void
     */
    private function addRowStyle(int $rowIndex, array $style): void
    {
        if (!isset($this->dataOptions[$rowIndex])) {
            $this->dataOptions[$rowIndex] = [];
        }

        // $styleをマージする
        $this->dataOptions[$rowIndex] = array_merge($this->dataOptions[$rowIndex], $style);
    }
}
