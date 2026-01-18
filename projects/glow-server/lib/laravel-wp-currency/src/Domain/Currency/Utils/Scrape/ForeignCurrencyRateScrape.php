<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Utils\Scrape;

use DOMDocument;
use DOMXPath;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use WonderPlanet\Domain\Currency\Utils\CommonUtility;

/**
 * 外貨為相場スクレイピング処理
 * 下記サイトから最新の月末外貨為替相場データを取得する
 */
class ForeignCurrencyRateScrape
{
    /**
     * スクレイピング先のURL
     * クエリパラメータは対象年月を指定する
     * 例:2023年1月末の相場データ -> id=2301
     */
    private const URL = 'https://www.murc-kawasesouba.jp/fx/monthend/index.php?id=';

    /**
     * TWD, MYRの外貨は以下の画面からスクレイピングする
     */
    private const EXCEL_URL = 'https://www.murc-kawasesouba.jp/fx/ref_rate.html';
    /**
     * TWD, MYRの外貨取得用エクセルファイルの仮置き場
     */
    private const TEMP_EXCEL_DIR_PATH = 'tmp_excel';

    /**
     * explodeチェック用
     */
    private const VALIDATE_EXPLODE_COUNT = 8;

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
     * @param int $year
     * @param int $month
     * @param string $url
     * @param bool $needMakeQueryParameter
     * @return string|bool
     */
    protected function getContent(
        int $year,
        int $month,
        string $url = self::URL,
        bool $needMakeQueryParameter = true
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

        if ($needMakeQueryParameter) {
            $parameter = $this->makeQueryParameter($year, $month);
            $urlParameter = $url . $parameter;
        } else {
            $urlParameter = $url;
        }
        if (!isset(self::$contentCash[$urlParameter])) {
            // file_get_contentsを何度も実行しないように、urlをkeyに取得データをキャッシュする
            self::$contentCash[$urlParameter] = file_get_contents($urlParameter);
        }
        return self::$contentCash[$urlParameter];
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
     * 年の下2桁と月の値からスクレピングページのクエリパラメータを生成
     *
     * @param int $year
     * @param int $month
     * @return string
     */
    protected function makeQueryParameter(int $year, int $month): string
    {
        // 年の下2桁と月の値からスクレピングページのクエリパラメータを生成
        $yearLastTwoDigits = substr((string)$year, -2);
        // 月が1桁の場合は、先頭に0を追加
        $monthFormatted = str_pad((string)$month, 2, "0", STR_PAD_LEFT);
        return $yearLastTwoDigits . $monthFormatted;
    }

    /**
     * htmlコードを解析して登録に必要なデータを取得
     *
     * @param int $year
     * @param int $month
     * @return Collection<int, mixed>
     */
    public function parse(int $year, int $month): Collection
    {
        $htmlCode = $this->getContent($year, $month);
        if ($htmlCode === false) {
            Log::error('外貨為替収集情報取得:htmlCodeがfalse');
            throw new \Exception('外貨為替収集情報取得:htmlCodeがfalse');
        }

        $xpath = $this->getDOMXPath($htmlCode);

        $h2elements = $xpath->query('//h2');
        if ($h2elements->count() === 0) {
            Log::error('外貨為替収集情報取得:h2テキストの解析でエラー', [$h2elements]);
            throw new \Exception('外貨為替収集情報取得:h2テキストの解析でエラー');
        }

        // 最新の月末為替情報となっているかチェック
        $pattern = "/(\d{4})年((?:0?[1-9]|1[0-2]))月/u";
        foreach ($h2elements as $h2) {
            // <h2>のテキストを取得
            $h2Text = $h2->textContent;

            // 「yyyy年mm月末日および月中平均相場」の部分の年月をチェック
            if (!preg_match($pattern, $h2Text, $matches)) {
                // 該当の文言でなければスキップ
                continue;
            }

            if ($year !== (int)$matches[1] || $month !== (int)$matches[2]) {
                // 年と月が取得したい情報でなければ処理を終える
                Log::info('外貨為替収集情報取得:指定年月の情報がなかった', [$year, $month, $matches]);
                return collect();
            }
        }

        // <h2>直下のclass="data-table7"のtableを検索
        $tableData = $xpath->query('//h2/following-sibling::table[@class="data-table7"][1]');
        if ($tableData->count() === 0) {
            Log::error('外貨為替収集情報取得:tableデータの解析でエラー', [$tableData]);
            throw new \Exception('外貨為替収集情報取得:tableデータの解析でエラー');
        }

        $elementData = $tableData->item(0);
        if (is_null($elementData)) {
            Log::error('外貨為替収集情報取得:tableデータの解析結果がnull', [$elementData]);
            throw new \Exception('外貨為替収集情報取得:tableデータの解析結果がnull');
        }

        // タブ文字の削除
        $values = preg_replace("/\t+/", '', $elementData->nodeValue);

        // 改行を全て\nに変換して統一
        $values = str_replace("\r\n", "\n", $values);

        // 2行以上の改行で分割して配列化
        $values = preg_split("/(\n){2,}/", trim($values));

        $currencyRateCollection = collect();
        foreach ($values as $key => $value) {
            if ($key === 0) {
                // 最初のデータは列名なのでスキップ
                continue;
            }

            // 改行で文字分割して取得
            $explodes = explode("\n", trim($value));

            if (count($explodes) !== self::VALIDATE_EXPLODE_COUNT) {
                Log::error('外貨為替収集情報取得:tableデータの解析結果が想定と異なる', [$explodes]);
                throw new \Exception('外貨為替収集情報取得:tableデータの解析結果が想定と異なる');
            }
            // 文言からスペースと*が存在していたら除外する
            // $currencyは単語の間の可能性があるので半角スペースは削らない
            $currency = str_replace(['　', '*'], '', $explodes[0]);
            $currencyName = str_replace(['　', ' ', '*'], '', $explodes[1]);
            $currencyCode = str_replace(['　', ' ', '*'], '', $explodes[2]);
            $perUnit = str_replace(['　', ' ', '*'], '', $explodes[3]);
            $tts = str_replace(['　', ' ', '*'], '', $explodes[4]);
            $ttb = str_replace(['　', ' ', '*'], '', $explodes[5]);

            if ($tts === 'unquoted' || $ttb === 'unquoted') {
                // サイト側で値を表示していない場合は'unquoted'と表示される為
                // ttsとttbが'unquoted'だった場合はスキップする
                continue;
            }

            // ttsが数字(浮動小数点付き)に変換できるかチェック
            if (!filter_var($tts, FILTER_VALIDATE_FLOAT)) {
                // $ttsが数字への変換ができない文字列だった
                Log::error('外貨為替収集情報取得:ttsが数字に変換できなかった', [$tts]);
                throw new \Exception('外貨為替収集情報取得:ttsが数字に変換できなかった');
            }

            if (!filter_var($ttb, FILTER_VALIDATE_FLOAT)) {
                // $ttbが数字への変換ができない文字列だった
                Log::error('外貨為替収集情報取得:ttbが数字に変換できなかった', [$ttb]);
                throw new \Exception('外貨為替収集情報取得:ttbが数字に変換できなかった');
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

    /**
     * excelファイルから必要データを取得
     * TWD, MYRを取得
     *
     * @param int $year
     * @param int $month
     * @return Collection<int, mixed>
     */
    public function parseLocalReferenceExchangeRateByExcel(int $year, int $month): Collection
    {
        // htmlCodeを取得
        $htmlCode = $this->getContent($year, $month, self::EXCEL_URL, false);
        if ($htmlCode === false) {
            Log::error('外貨為替収集情報取得:htmlCodeがfalse');
            throw new \Exception('外貨為替収集情報取得:htmlCodeがfalse');
        }

        // リンク要素を取得
        $xpath = $this->getDOMXPath($htmlCode);
        $links = $xpath->query('//ul/li/a');
        $fileUrl = "";
        /** @var \DOMElement $link */
        foreach ($links as $link) {
            $linkText = $link->textContent;
            // 年が違う場合はスキップ
            if (!str_contains($linkText, (string)$year)) {
                continue;
            }
            // 国名が含まれていない場合はスキップ
            if (!str_contains($linkText, "マレーシア、中国、台湾")) {
                continue;
            }
            $fileUrl = $link->getAttribute('href');
            break;
        }
        if ($fileUrl === "") {
            Log::error('外貨為替収集情報取得:fileUrlがない');
            throw new \Exception('外貨為替収集情報取得:fileUrlがない');
        }

        // ファイルをダウンロードして仮置きする
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $data = $this->getExcelDataFromLocalReferenceExchangeRate($fileUrl, $disk, $month);

        // TTSとTTBを取得して返す
        return $this->getTtbAndTtsDataFromExcelData($data);
    }

    /**
     * 現地参考為替相場のexcelファイルを取得して仮ファイルとして保存する
     * @param string $fileUrl
     * @param FilesystemAdapter $disk
     * @param int $month
     * @return array<mixed>
     */
    protected function getExcelDataFromLocalReferenceExchangeRate(
        string $fileUrl,
        FilesystemAdapter $disk,
        int $month,
    ): array {
        // fileUrlが相対パスの場合、絶対パスに変える
        $fileUri = $this->convertToUri($fileUrl, self::EXCEL_URL);

        // excelファイルを読み込んで仮置きする
        $fileData = file_get_contents($fileUri);
        if ($fileData === false) {
            // ファイルの取得失敗
            Log::error('外貨為替収集情報取得:ファイル取得失敗');
            throw new \Exception('外貨為替収集情報取得:ファイル取得失敗');
        }
        // 仮置きファイル用のディレクトリを作成
        if (!$disk->exists(self::TEMP_EXCEL_DIR_PATH)) {
            // ディレクトリがなければ作成する
            $result = $disk->makeDirectory(self::TEMP_EXCEL_DIR_PATH);
            if ($result === false) {
                // ディレクトリ作成失敗
                Log::error('外貨為替収集情報取得:ディレクトリ作成失敗');
                throw new \Exception('外貨為替収集情報取得:ディレクトリ作成失敗');
            }
        }
        // 仮置きするファイル名を生成してファイルを置く
        $tmpFilePath = tempnam($disk->path(self::TEMP_EXCEL_DIR_PATH), "local_reference_tmp") . ".xls";
        $result = file_put_contents($tmpFilePath, $fileData);
        if ($result === false) {
            // ファイル仮置き失敗
            Log::error('外貨為替収集情報取得:ファイルの配置失敗');
            throw new \Exception('外貨為替収集情報取得:ファイルの配置失敗');
        }

        // 仮置きしたファイルから中身を取得し、仮置きファイルを削除する
        $reader = new XlsReader();
        $spreadsheet = $reader->load($tmpFilePath);
        // 一時的に置いたファイルを削除
        $this->deleteTmpFile($tmpFilePath, $disk);
        // 読み込むシートを指定
        $sheet = $spreadsheet->getSheetByName($month . '月');
        if (is_null($sheet)) {
            // ファイル内に対象月がないエラー
            Log::error('外貨為替収集情報取得:対象月のシート取得失敗');
            throw new \Exception('外貨為替収集情報取得:対象月のシート取得失敗');
        }
        // データを取得
        return $sheet->rangeToArray('A1:L39');
    }

    /**
     * 仮置きしたファイルを読み込んでデータを取得する
     * @param array<mixed> $data
     * @return Collection<int, mixed>
     */
    protected function getTtbAndTtsDataFromExcelData(array $data): Collection
    {
        $currencyRate = [];
        $twdTts = 0;
        $twdTtb = 0;
        $myrTts = 0;
        $myrTtb = 0;
        // 月末の情報を取得するため配列を逆にし月末情報を取りやすくする
        $reverseData = array_reverse($data);
        foreach ($reverseData as $row) {
            if ($row[0] === "" || !is_numeric($row[0])) {
                continue;
            }

            // TWDにまだデータがなく、かつ数値を取得できる場合は設定する
            if (
                ($twdTts === 0 || $twdTtb === 0) &&
                (($row[6] !== "" && !is_null($row[6])) || ($row[7] !== "" && !is_null($row[7])))
            ) {
                $twdTts = $row[6];
                $twdTtb = $row[7];
            }

            // MYRにまだデータがなく、かつ数値を取得できる場合は設定する
            if (
                ($myrTts === 0 || $myrTtb === 0) &&
                (($row[10] !== "" && !is_null($row[10])) || ($row[11] !== "" && !is_null($row[11])))
            ) {
                $myrTts = $row[10];
                $myrTtb = $row[11];
            }

            // 土日祝等の理由で取得できない可能性があるため、データがない場合は1日前のデータを参照する
            if ($twdTts === 0 || $twdTtb === 0 || $myrTts === 0 || $myrTtb === 0) {
                continue;
            } else {
                // 全て何かしら値が入ったらbreak
                break;
            }
        }

        // コレクションに格納
        // 小数点第七位で四捨五入し、小数点第六位までにする
        // TWDはPER YEN、MYRはPER 100yenとなっているため、1通貨あたり何円という単位に合わせるため数値が逆転しています
        $perYen = "1";
        $per100Yen = "100";
        $twdTts = CommonUtility::calcAndRoundRateForTWDAndYMR($perYen, $twdTts);
        $twdTtb = CommonUtility::calcAndRoundRateForTWDAndYMR($perYen, $twdTtb);
        $myrTts = CommonUtility::calcAndRoundRateForTWDAndYMR($per100Yen, $myrTts);
        $myrTtb = CommonUtility::calcAndRoundRateForTWDAndYMR($per100Yen, $myrTtb);
        $currencyRate[] = [
            'currency' => "New Taiwan Dollar",
            'currencyName' => "台湾ドル",
            'currencyCode' => "TWD",
            'tts' => $twdTts,
            'ttb' => $twdTtb,
        ];
        $currencyRate[] = [
            'currency' => "Malaysian Ringgit",
            'currencyName' => "マレーシア・リンギット",
            'currencyCode' => "MYR",
            'tts' => $myrTts,
            'ttb' => $myrTtb,
        ];
        return collect($currencyRate);
    }

    /**
     * 以下を参考
     * https://miner.hatenablog.com/entry/187
     * スクレイピングなどで画像URLを取得する時に使うために
     * ベースURLを元に相対パスから絶対パスに変換する関数
     *
     * @param string $targetPath 変換する相対パス
     * @param string $base ベースとなるパス
     * @return string 絶対パスに変換済みのパス
     */
    protected function convertToUri(string $targetPath, string $base): string
    {
        $component = parse_url($base);
        $directory = preg_replace('!/[^/]*$!', '/', $component["path"]);

        switch (true) {
            // 絶対パスのケース（簡易版)
            case preg_match("/^http/", $targetPath):
                $uri = $targetPath;
                break;

            // 「//exmaple.jp/aa.jpg」のようなケース
            case preg_match("/^\/\/.+/", $targetPath):
                $uri = $component["scheme"] . ":" . $targetPath;
                break;

            // 「/aaa/aa.jpg」のようなケース
            // 「/」のケース
            case preg_match("/^\/[^\/].+/", $targetPath):
            case preg_match("/^\/$/", $targetPath):
                $uri = $component["scheme"] . "://" . $component["host"] . $targetPath;
                break;

            // 「./aa.jpg」のようなケース
            case preg_match("/^\.\/(.+)/", $targetPath, $matches):
                $uri = $component["scheme"] . "://" . $component["host"] . $directory . $matches[1];
                break;

            //「aa.jpg」のようなケース（[3]と同じ）
            case preg_match("/^([^\.\/]+)(.*)/", $targetPath, $matches):
                $uri = $component["scheme"] . "://" . $component["host"] . $directory . $matches[1] . $matches[2];
                break;

            // 「../aa.jpg」のようなケース
            case preg_match("/^\.\.\/.+/", $targetPath):
                //「../」をカウント
                preg_match_all("!\.\./!", $targetPath, $matches);
                $nest = count($matches[0]);
                //ベースURLのディレクトリを分解してカウント
                $dir = preg_replace('!/[^/]*$!', '/', $component["path"]) . "\n";
                $dir_array = explode("/", $dir);
                array_shift($dir_array);
                array_pop($dir_array);
                $dir_count = count($dir_array);
                $count = $dir_count - $nest;
                $pathTo = "";
                $i = 0;
                while ($i < $count) {
                    $pathTo .= "/" . $dir_array[$i];
                    $i++;
                }
                $file = str_replace("../", "", $targetPath);
                $uri = $component["scheme"] . "://" . $component["host"] . $pathTo . "/" . $file;
                break;

            default:
                $uri = $targetPath;
        }
        return $uri;
    }

    /**
     * 仮置きしたexcelファイルを削除
     * @param string $tmpFilePath
     * @param FilesystemAdapter $disk
     * @return void
     */
    protected function deleteTmpFile(string $tmpFilePath, FilesystemAdapter $disk): void
    {
        // 一時的に置いたファイルとディレクトリを削除
        $result = $disk->delete($tmpFilePath);
        if ($result === false) {
            // ファイル削除失敗
            Log::error('外貨為替収集情報取得:仮置きしたファイルの削除失敗');
            throw new \Exception('外貨為替収集情報取得:仮置きしたファイルの削除失敗');
        }
    }
}
