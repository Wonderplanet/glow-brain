<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class GenerateCreateMstMigration extends Command
{
    protected $signature = 'generate:api:create-mst-migration {tableDataNames*}';
    protected $description = 'モデルクラス(アッパーキャメルケース)を指定して、glow-schemaのymlファイルを参照して、mstテーブルを作成するマイグレーションファイルを生成する';

    private string $ymlDir = '/var/local/glow-schema/Schema';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $tableDataNames = $this->argument('tableDataNames');

        $mergedYmlData = $this->getAndMergeYmlData();

        $ymlSchemas = [];

        $dataList = $mergedYmlData['data'];
        foreach ($dataList as $modelName => $params) {
            // マスターテーブルの情報のみで処理する
            if (
                in_array($modelName, ['Mst', 'MstI18n', 'Opr', 'OprI18n'])
                || !in_array($modelName, $tableDataNames)
            ) {
                continue;
            }

            // ymlのスキーマ情報取得
            $ymlColumns = collect($params)->mapWithKeys(function ($dataType, $columnName) {
                return [$columnName => $dataType];
            });
            $ymlSchemas[$modelName] = $ymlColumns;
        }

        $this->makeMigrationFile($ymlSchemas, $mergedYmlData['enum']);

        return 0;
    }

    /**
     * glow-schemaのymlのデータを取得し、1つの連想配列としてマージする
     * @return array
     */
    private function getAndMergeYmlData(): array
    {
        $files = File::allFiles($this->ymlDir);

        $merged = [];
        foreach ($files as $file) {
            // ymlファイル以外はスキップ
            if ($file->getExtension() !== 'yml' && $file->getExtension() !== 'yaml') {
                continue;
            }

            $ymlData = Yaml::parseFile($file->getPathname());

            // キーがdata,enum,apiでそれぞれまとめる
            foreach ($ymlData as $key => $dataList) {
                if (!isset($merged[$key])) {
                    $merged[$key] = [];
                }

                switch ($key) {
                    case 'data':
                        foreach ($dataList as $data) {
                            $params = collect($data['params'])->mapWithKeys(function ($param) {
                                return [$param['name'] => $param['type']];
                            })->toArray();
                            $merged[$key][$data['name']] = $params;
                        }
                        break;
                    case 'enum':
                        foreach ($dataList as $data) {
                            $params = collect($data['params'])->pluck('name')->toArray();
                            $merged[$key][$data['name']] = $params;
                        }
                        break;
                    case 'api':
                        foreach ($dataList as $data['actions']) {
                            $merged[$key][$data['name']] = $data;
                        }
                        $merged[$key][$data['name']] = $data['params'];
                        break;
                }
            }
        }

        return $merged;
    }

    /**
     * マイグレーションファイルを作成する
     *
     * @param array $ymlSchemas
     * @param array $enumList
     * @return void
     */
    private function makeMigrationFile(array $ymlSchemas, array $enumList): void
    {
        // マイグレーションファイル名を生成
        $tableDataNames = array_keys($ymlSchemas);
        $tableDataNames = array_map(function ($tableName) {
            return Str::of($tableName)->snake()->plural()->__toString();
        }, $tableDataNames);
        $now = CarbonImmutable::now();
        $fileName = sprintf('%s_create_table_%s.php', $now->format('Y_m_d_His'), implode('_', $tableDataNames));
        $filePath = app_path("../database/migrations/mst/{$fileName}");

        // マイグレーションファイルの内容を生成

        // up: 新規テーブルとして追加する
        // down: テーブルを削除する
        $upContent = '';
        $downContent = '';

        foreach ($ymlSchemas as $tableName => $columns) {
            $dbTableName = Str::of($tableName)->snake()->plural()->__toString();

            $upContent .= "\n        Schema::create('{$dbTableName}', function (Blueprint \$table) {";

            // idとrelease_key列の追加は必須
            $upContent .= "\n            \$table->string('id')->primary();";
            $upContent .= "\n            \$table->bigInteger('release_key')->default(1);";

            // ymlスキーマの各列を1つずつ確認して、マイグレーションファイルの記述を追加していく
            $ymlColumns = $ymlSchemas[$tableName];
            foreach ($ymlColumns as $ymlColumnName => $ymlColumnType) {
                // カラム名をスネークケースに変換（ymlはアッパーキャメル、dbはスネークケースのため）
                $ymlColumnName = Str::of($ymlColumnName)->snake()->__toString();

                // idとrelease_keyは既に追加しているのでスキップ
                if (in_array($ymlColumnName, ['id', 'release_key'])) {
                    continue;
                }

                // ymlでは、型指定だけでなく、「<型指定文字列>?」の形式で記述されていることがあるため、nullableかどうかも判定
                $type = str_replace('?', '', $ymlColumnType);
                $isNullable = strpos($ymlColumnType, '?') !== false;

                // enumの場合は、enumの値を取得
                $enumData = isset($enumList[$ymlColumnType])
                    ? $enumList[$ymlColumnType] : null;
                if ($enumData !== null) {
                    $type = 'enum';
                }

                $columnContent = "\n            \$table->";

                // 各データタイプに応じて、マイグレーションファイルの記述を追加
                switch ($type) {
                    case 'enum':
                        $columnContent .= sprintf("enum('%s', [%s])", $ymlColumnName, implode(", ", array_map(function ($value) {
                            return "'{$value}'";
                        }, $enumData)));
                        break;
                    case 'string':
                        $columnContent .= "string('{$ymlColumnName}')";
                        break;
                    case 'DateTimeOffset':
                        $columnContent .= "timestampTz('{$ymlColumnName}')";
                        break;
                    case 'float':
                    case 'double':
                        // デフォルトとして10, 2を指定
                        $columnContent .= "decimal('{$ymlColumnName}', 10, 2)";
                        break;
                    case 'long':
                        $columnContent .= "bigInteger('{$ymlColumnName}')";
                        break;
                    case 'int':
                        $columnContent .= "integer('{$ymlColumnName}')";
                        break;
                    case 'bool':
                        $columnContent .= "unsignedTinyInteger('{$ymlColumnName}')";
                        break;
                }

                // nullableかどうかによって、nullable()を指定
                if ($isNullable) {
                    $columnContent .= '->nullable(true)';
                } else {
                    // $type=stringなら、default('')を指定
                    if ($type === 'string') {
                        $columnContent .= "->default('')";
                    }
                }

                $upContent .= $columnContent . ";";
            }

            $upContent .= "\n        });";
            $downContent .= "\n        Schema::dropIfExists('{$dbTableName}');";
        }

        // マイグレーションファイルのテンプレートに、生成した内容を埋め込む
        $template = <<<PHP
<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected \$connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $upContent
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $downContent
    }
};

PHP;

        // マイグレーションファイルを生成
        if (!file_put_contents($filePath, $template)) {
            $this->error("Failed to create file at {$filePath}");
            return;
        }

        $this->info("Migration file created at {$filePath}");
    }
}
