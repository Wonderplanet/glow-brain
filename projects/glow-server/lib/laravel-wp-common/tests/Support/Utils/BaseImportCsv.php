<?php

namespace WonderPlanet\Tests\Support\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

/**
 * CSVインポートの規定クラス
 *
 * ディレクトリの位置などがプロダクトによって違うので、使用するときはこれを継承して
 * ディレクトリの位置を指定すること
 *
 * このクラスのユニットテストおよび組み込みは、各プロダクトのテスト側で行う。
 * クラスの性質上DBアクセスが必要となるため、Common側では環境を用意しづらいため。
 */
abstract class BaseImportCsv
{
    /**
     * fixtureのあるルートディレクトリを取得する
     *
     * デフォルトディレクトリは、getFixtureRootDir() . '/default'となる
     * Fixtureディレクトリは、getFixtureRootDir() . '/クラス名/fixture名'となる
     *
     * @return string
     */
    abstract public function getFixtureRootDir(): string;

    /**
     * Factoryクラスを配置しているディレクトリパスを取得する
     *
     * @return string
     */
    abstract public function getModelClassPass(): string;

    /**
     * 生成したモデルクラス保存用
     * @var array
     */
    protected array $models = [];

    private array $customCasts = [
        'date' => 'date:Y-m-d',
        'datetime' => 'datetime:Y-m-d H:i:s',
        'immutable_date' => 'immutable_date:Y-m-d',
        'immutable_datetime' => 'immutable_datetime:Y-m-d H:i:s',
    ];
    /**
     * defaultディレクトリ内を対象にCSVをインポートする
     *
     * @return void
     */
    public function execCreateFixtureDataDefault(): void
    {
        $dir = implode(DIRECTORY_SEPARATOR, [$this->getFixtureRootDir(), 'default']) . DIRECTORY_SEPARATOR;
        $this->importFromDirectries($dir);
    }

    /**
     * クラス名・fixture名のディレクトリを対象にCSVをインポートする
     *
     * @param string $className
     * @param string $fixtureName
     *
     * @return void
     */
    public function execCreateFixtureData(string $className, string $fixtureName): void
    {
        $dir = implode(DIRECTORY_SEPARATOR, [$this->getFixtureRootDir(), $className, $fixtureName]) . DIRECTORY_SEPARATOR;
        $this->importFromDirectries($dir);
    }

    /**
     * 指定したディレクトリ内からコネクション名・テーブル名・ファイル名を
     * 特定してインポート
     *
     * @param string $dir CSVが格納されているディレクトへのパス
     *
     * @return void
     */
    private function importFromDirectries(string $dir): void
    {
        $connectionList = File::directories($dir);
        foreach ($connectionList as $connection) {
            $connection = basename($connection);
            $csvList = File::files($dir . $connection);
            if (!$csvList) {
                continue;
            }
            foreach ($csvList as $csv) {
                $table = str_replace('.csv', '', $csv->getFilename());
                $file = $dir . $connection . '/' . $csv->getFilename();

                // factoryクラス取得の為、テーブル名をもとにモデルクラスを生成
                $this->createModelClassByTable($table);
                // インポート実行
                $this->import($file, $connection, $table);
            }
        }
    }

    /**
     * テーブル名をもとにモデルクラスを生成する
     * 生成したモデルクラスは全テストで保持できるようにキャッシュする
     *
     * @param string $table
     * @return void
     */
    private function createModelClassByTable(string $table): void
    {
        if (isset($this->models[$table])) {
            // すでにモデルクラスを保持している場合はスキップ
            return;
        }

        // テーブル名からクラスファイル名に変換する
        $classFileByTable = Str::studly(Str::singular($table));

        // 対象のモデルクラスを検索してパスを取得する
        $matchFilePathStr = shell_exec("find {$this->getModelClassPass()} -name '{$classFileByTable}.php'");

        if (is_null($matchFilePathStr) && str_contains($table, 's_i18n')) {
            // モデルクラスパスが見つからずi18n系テーブルの場合はi18nテーブル名で検索する
            // i18n系のテーブルはモデルクラス少し異なる可能性があるのでそれ用に検索しなおす
            // 例) mng_messages_i18n -> opr_message_i18n
            $tableI18n = str_replace(['s_i18n'], ['_i18n'], $table);

            $classFileByTableI18n = Str::studly(Str::singular($tableI18n));
            $matchFilePathStr = shell_exec("find {$this->getModelClassPass()} -name '{$classFileByTableI18n}.php'");
        }

        if (is_null($matchFilePathStr)) {
            // ここまでで見つからない場合は以降の処理はスキップする
            return;
        }

        // 「\n」で文字を区切って配列化
        $matchFilePaths = explode("\n", $matchFilePathStr);
        // 空の要素を取り除く
        $matchFilePaths = array_filter($matchFilePaths);

        // 該当するモデルクラスが2件以上ある場合を考慮し先頭のクラスを取得する
        $matchFilePath = array_pop($matchFilePaths);

        // パス内の文字を置き換えて名前解決できるクラスパスに変換
        // 例)./app/Domain/User/Models/UsrUser.php -> App\Domain\User\Models\UsrUser
        $classPass = str_replace(['./app', '/', '.php'], ['App', '\\', ''], $matchFilePath);
        if (!class_exists($classPass)) {
            // クラス名の生成が正しくできなかった場合はエラーにせず処理をスキップ
            return;
        }

        // クラスパスからモデルクラスのオブジェクトを生成
        $modelClass = resolve($classPass);

        // castsにDateTime系の型がある場合は変更する
        $casts = $modelClass->getCasts();
        $mergeCasts = [];
        foreach ($casts as $column => $cast) {
            if (isset($this->customCasts[$cast])) {
                $mergeCasts[$column] = $this->customCasts[$cast];
            }
        }
        $modelClass->mergeCasts($mergeCasts);

        // モデルクラスを保持する
        $this->models[$table] = $modelClass;
    }

    /**
     * CSVを読み込んでインポート
     *
     * @param string $file ファイル名（パス込）
     * @param string $connection コネクション名
     * @param string $table テーブル名
     *
     * @return void
     */
    private function import(string $file, string $connection, string $table): void
    {
        $fp = fopen($file, 'r');
        $columns = [];
        while (($csv = fgetcsv($fp)) !== false) {
            if (!$columns) {
                $csv[0] = $this->removeBom($csv[0]);
                $columns = $csv;
                continue;
            }
            $values = [];
            foreach ($csv as $idx => $val) {
                if ($val === '') {
                    // $valが空文字で設定されている場合はスキップ
                    // nullを設定したい場合はfactory側でデフォルト値として設定する
                    continue;
                }
                $values[$columns[$idx]] = $val;

            }
            $values = $this->setFactoryDefault($table, $values);
            $this->upsert($connection, $table, $columns, $values);
        }
    }

    /**
     * 対象tableをもとに、csv上で未設定のカラムデータにFactoryクラスのデフォルト値をセットする
     *
     * @param string $table
     * @param array $values
     * @return array
     */
    private function setFactoryDefault(string $table, array $values): array
    {
        if (!isset($this->models[$table])) {
            // 対象のモデルクラスを生成してなければ$valuesをそのまま返す
            return $values;
        }

        $model = $this->models[$table];

        $makeModel = $model::factory()->make($values);
        // DateTime型などのキャストを考慮して変更したCastsをマージして適応する
        $makeModel = $makeModel->mergeCasts($model->getCasts());
        return $makeModel->toArray();
    }

    /**
     * upsertの実行
     *
     * @param string $connection コネクション名
     * @param string $table テーブル名
     * @param array $columns カラム名の配列
     * @param array $values upsertする値の配列
     *
     * @return void
     */
    private function upsert(string $connection, string $table, array $columns, array $values): void
    {
        try {
            DB::connection($connection)->table($table)->upsert($values, [], $columns);
        } catch (Exception $e) {
            Log::error(sprintf('CsvImport failed: %s', $e->getMessage()));
            Log::error($e);
            throw $e;
        }
    }

    /**
     * BOM取り
     *
     * @param string $text 文字列
     *
     * @return string BOM削除後の文字列
     */
    private function removeBom(string $text): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $text);
    }
}
