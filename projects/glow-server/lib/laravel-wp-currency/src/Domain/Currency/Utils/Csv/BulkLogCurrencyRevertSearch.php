<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils\Csv;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * 一次通貨返却(一括)の対象検索結果Excel出力
 */
class BulkLogCurrencyRevertSearch implements FromCollection, WithTitle, ShouldAutoSize
{
    use Exportable;

    /**
     * 出力するヘッダ
     *
     * @var array<string, string>
     */
    protected static array $header = [
        'ユーザーID' => 'usr_user_id',
        'コンテンツ消費日時' => 'consumed_at',
        '消費コンテンツタイプ' => 'trigger_type',
        '消費コンテンツID' => 'trigger_id',
        '消費コンテンツ名' => 'trigger_name',
        'リクエストID' => 'request_id',
        '消費有償一次通貨数(合計)' => 'sum_log_change_amount_paid',
        '消費無償一次通貨数(合計)' => 'sum_log_change_amount_free',
        '有償一次通貨の消費ログID' => 'log_currency_paid_ids',
        '無償一次通貨の消費ログID' => 'log_currency_free_ids',
    ];

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
     * @var Collection<string, mixed>
     */
    private Collection $dataCollection;

    /**
     * コンストラクタ
     *
     * @param Collection<string, mixed> $dataCollection
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable $endAt
     * @param bool $isIncludeSandbox
     */
    public function __construct(
        Collection $dataCollection,
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        bool $isIncludeSandbox
    ) {
        $this->dataCollection = $dataCollection;

        // 開始日と終了日からファイル名を生成
        $startTime = $startAt->setTimezone(CurrencyConstants::OUTPUT_TZ)->format('YmdHis');
        $endTime = $endAt->setTimezone(CurrencyConstants::OUTPUT_TZ)->format('YmdHis');

        $this->fileName = '一次通貨返却対象データレポート_' . $startTime . '-' . $endTime;
        if ($isIncludeSandbox) {
            $this->fileName .= '_' . 'サンドボックスデータ含む';
        }
        $this->fileName .= '.csv';
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
        // ヘッダ行生成
        $list[] = self::getHeader();

        if ($this->dataCollection->count() === 0) {
            $list[] = ['対象データが存在しません'];
            return collect($list);
        }

        foreach ($this->dataCollection as $rowData) {
            // データ行生成
            $row = [];
            foreach (self::getHeaderKey() as $key) {
                $row[] = $rowData[$key];
            }

            $list[] = $row;
        }
        return collect($list);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return '一次通貨返却対象データ';
    }

    /**
     * ヘッダを取得する
     *
     * @return array<string>
     */
    public static function getHeader(): array
    {
        return array_keys(static::$header);
    }

    /**
     * ヘッダに対応するキーを取得する
     *
     * @return array<string>
     */
    public static function getHeaderKey(): array
    {
        return array_values(static::$header);
    }

    /**
     * ヘッダとキーを取得する
     *
     * @return array<string, string>
     */
    public static function getHeaderAndKey(): array
    {
        return static::$header;
    }
}
