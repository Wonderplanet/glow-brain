<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils\Scrape;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * 本日の為替相場スクレイピング処理
 * 下記サイトから最新の月末外貨為替相場データを取得する
 */
class ForeignCurrencyDailyRateScrape
{
    /**
     * 当日為替相場のURL
     */
    private const TODAY_URL = 'https://www.murc-kawasesouba.jp/fx/index.php';

    /**
     * 通貨単位のデフォルト値リスト
     */
    private const DEFAULT_PER_UNIT_LIST = [
        'IDR' => '100unit',
        'KRW' => '100unit',
    ];

    /**
     * 上記リスト以外の通貨単位のデフォルト値
     */
    private const DEFAULT_PER_UNIT = '1unit';

    /**
     * getContentキャッシュ用
     * プロセス内でfile_get_contentsが何度も実行されないように、取得データを保持する
     *
     * @var array<string, mixed>
     */
    private static array $contentCash = [];

    /**
     * スクレイピング先のhtmlコードを取得
     *
     * @param string $url
     * @return string|bool
     * @throws \Exception
     */
    protected function getContent(
        string $url = self::TODAY_URL,
    ): string|bool {
        // ユニットテストで取得しようとした場合はエラーにする
        // 必要であればmockを経由させて、このメソッドを置き換えて使うこと
        if (
            config('app.env') === 'local_test'
            || config('app.env') === 'admin_test'
            || config('app.env') === 'testing'
        ) {
            // URLがローカルファイルのパスであれば通す
            // file:://, /, ./, ../のいずれかで始まる場合はローカルファイルとして扱う
            if (!preg_match('/^(file:\/\/|\/|\.\/|\.\.\/)/', $url)) {
                // テスト環境での外部通信は禁止
                Log::error('外貨為替収集情報取得:テスト環境での外部通信は禁止: ' . $url);
                throw new \Exception('外貨為替収集情報取得:テスト環境での外部通信は禁止: ' . $url);
            }
        }

        if (!isset(self::$contentCash[$url])) {
            // file_get_contentsを何度も実行しないように、urlをkeyに取得データをキャッシュする
            self::$contentCash[$url] = file_get_contents($url);
        }
        return self::$contentCash[$url];
    }

    /**
     * DOMXPathオブジェクト生成
     *
     * @param string $htmlCode
     * @return DOMXPath
     */
    protected function getDOMXPath(string $htmlCode): \DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($htmlCode);
        return new DOMXPath($dom);
    }

    /**
     * htmlコードを解析して登録に必要なデータを取得
     *
     * @return Collection<int, mixed>
     * @throws \Exception
     */
    public function parse(int $year, int $month, int $day): Collection
    {
        $htmlCode = $this->getContent();
        if ($htmlCode === false) {
            Log::error('本日の為替収集情報取得:htmlCodeがfalse');
            throw new \Exception('本日の為替収集情報取得:htmlCodeがfalse');
        }

        $xpath = $this->getDOMXPath($htmlCode);

        $h2elements = $xpath->query('//h2');
        if ($h2elements->count() === 0) {
            Log::error('本日の為替収集情報取得:h2テキストの解析でエラー', [$h2elements]);
            throw new \Exception('本日の為替収集情報取得:h2テキストの解析でエラー');
        }

        // 最新の月末為替情報となっているかチェック
        $pattern = "/(\d{4})年((?:0?[1-9]|1[0-2]))月((?:0?[1-9]|[12][0-9]|3[01]))日/u";
        foreach ($h2elements as $h2) {
            // <h2>のテキストを取得
            $h2Text = $h2->textContent;

            // 「yyyy年mm月末日および月中平均相場」の部分の年月をチェック
            if (!preg_match($pattern, $h2Text, $matches)) {
                // 該当の文言でなければスキップ
                continue;
            }

            if ($year !== (int)$matches[1] || $month !== (int)$matches[2] || $day !== (int)$matches[3]) {
                // 年と月が取得したい情報でなければ処理を終える
                Log::info('本日の為替収集情報取得:指定年月日の情報がなかった', [$year, $month, $matches]);
                return collect();
            }
        }

        $tableClass = "data-table5";
        $tableData = $xpath->query('//h2/following-sibling::table[@class="' . $tableClass . '"][1]');
        if ($tableData->count() === 0) {
            Log::error('本日の為替収集情報取得:tableデータの解析でエラー', [$tableData]);
            throw new \Exception('本日の為替収集情報取得:tableデータの解析でエラー');
        }

        $elementData = $tableData->item(0);
        if (is_null($elementData)) {
            Log::error('本日の為替収集情報取得:tableデータの解析結果がnull', [$elementData]);
            throw new \Exception('本日の為替収集情報取得:tableデータの解析結果がnull');
        }

        // タブ文字の削除
        $values = preg_replace("/\t+/", '', $elementData->nodeValue);

        // 改行を全て\nに変換して統一
        $values = str_replace("\r\n", "\n", $values);

        // 2行以上の改行で分割して配列化
        $values = preg_split("/(\n){2,}/", trim($values));

        $validateExplodeCount = 5;
        $currencyRateCollection = collect();
        foreach ($values as $key => $value) {
            if ($key === 0) {
                // 最初のデータは列名なのでスキップ
                continue;
            }

            // 改行で文字分割して取得
            $explodes = explode("\n", trim($value));
            $explodeCount = count($explodes);

            // テーブルの末端の列に文字が入る場合と入らない場合があり、入る場合はその分のcountも増えるので、+1した数値でも比較する
            if (($explodeCount !== $validateExplodeCount) && ($explodeCount !== ($validateExplodeCount + 1))) {
                Log::error('本日の為替収集情報取得:tableデータの解析結果が想定と異なる', [$explodes]);
                throw new \Exception('本日の為替収集情報取得:tableデータの解析結果が想定と異なる');
            }

            // 文言からスペースと*が存在していたら除外する
            // $currencyは単語の間の可能性があるので半角スペースは削らない
            $currency = str_replace(['　', '*'], '', $explodes[0]);
            $currencyName = str_replace(['　', ' ', '*'], '', $explodes[1]);
            $currencyCode = str_replace(['　', ' ', '*'], '', $explodes[2]);
            $perUnit = self::DEFAULT_PER_UNIT_LIST[$currencyCode] ?? self::DEFAULT_PER_UNIT;
            $tts = str_replace(['　', ' ', '*'], '', $explodes[3]);
            $ttb = str_replace(['　', ' ', '*'], '', $explodes[4]);

            if ($tts === 'unquoted' || $ttb === 'unquoted') {
                // サイト側で値を表示していない場合は'unquoted'と表示される為
                // ttsとttbが'unquoted'だった場合はスキップする
                continue;
            }

            // ttsが数字(浮動小数点付き)に変換できるかチェック
            if (!filter_var($tts, FILTER_VALIDATE_FLOAT)) {
                // $ttsが数字への変換ができない文字列だった
                Log::error('本日の為替収集情報取得:ttsが数字に変換できなかった', [$tts]);
                throw new \Exception('本日の為替収集情報取得:ttsが数字に変換できなかった');
            }

            if (!filter_var($ttb, FILTER_VALIDATE_FLOAT)) {
                // $ttbが数字への変換ができない文字列だった
                Log::error('本日の為替収集情報取得:ttbが数字に変換できなかった', [$ttb]);
                throw new \Exception('本日の為替収集情報取得:ttbが数字に変換できなかった');
            }

            // 通貨単位でTTS, TTBを割って1通貨あたりのTTS, TTBになるように計算する
            $tts = CommonUtility::calcTtsOrTtbWithPerUnit($tts, $perUnit);
            $ttb = CommonUtility::calcTtsOrTtbWithPerUnit($ttb, $perUnit);

            // コレクションに格納
            $currencyRate = [
                'currency' => $currency,
                'currencyName' => $currencyName,
                'currencyCode' => $currencyCode,
                'tts' => $tts,
                'ttb' => $ttb,
            ];
            $currencyRateCollection->add($currencyRate);
        }

        return $currencyRateCollection;
    }
}
