<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Constants\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 全DBテーブルのCREATE TABLE文をエクスポートするコマンド
 *
 * local/testing/local_test 環境でのみ実行可能
 * マイグレーション実行後に自動でDDLファイルを更新し、
 * git diff で変更を検出できるようにする
 *
 * 出力ファイル:
 *   - master_tables_ddl.sql: mst, mng (MySQL管理、admin以外)
 *   - user_tables_ddl.sql: usr, log, sys (TiDB管理)
 */
class ExportDdlCommand extends Command
{
    protected $signature = 'db:export-ddl';

    protected $description = '全DBテーブルのCREATE TABLE文をエクスポート（local/testing/local_test環境のみ）';

    /**
     * TiDBテーブルのプレフィックス（論理DB分割用）
     */
    private const TIDB_PREFIXES = [
        'usr_' => 'usr',
        'log_' => 'log',
        'sys_' => 'sys',
    ];

    /**
     * エクスポート対象外のテーブル
     */
    private const EXCLUDED_TABLES = [
        'migrations',
    ];

    public function handle(): int
    {
        // 環境チェック: 対象外環境では何も出力せずに終了
        $env = config('app.env');
        if (!in_array($env, ['local', 'testing', 'local_test'], true)) {
            return 0;
        }

        $outputDir = database_path('schema');

        // 出力ディレクトリ作成
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $this->info('全DBテーブルのDDLをエクスポート中...');
        $this->newLine();

        // MySQL系（mst, mng）をエクスポート
        $this->exportMysqlDdl($outputDir);

        // TiDB系（usr, log, sys）をエクスポート
        $this->exportTidbDdl($outputDir);

        $this->newLine();
        $this->info("エクスポート完了!");

        return 0;
    }

    private function exportMysqlDdl(string $outputDir): void
    {
        $outputPath = $outputDir . '/master_tables_ddl.sql';

        $output = $this->generateHeader('Master Tables DDL (mst, mng)');

        // mst, mng のみ（admin は除外）
        $output .= $this->exportMysqlConnection(Database::MST_CONNECTION, 'mst');
        $output .= $this->exportMysqlConnection(Database::MNG_CONNECTION, 'mng');

        file_put_contents($outputPath, $output);

        $this->outputFileInfo($outputPath);
    }

    private function exportTidbDdl(string $outputDir): void
    {
        $outputPath = $outputDir . '/user_tables_ddl.sql';

        $output = $this->generateHeader('User Tables DDL (usr, log, sys)');

        // TiDB（プレフィックスで論理分割）
        foreach (self::TIDB_PREFIXES as $prefix => $alias) {
            $output .= $this->exportTidbTables(Database::TIDB_CONNECTION, $prefix, $alias);
        }

        file_put_contents($outputPath, $output);

        $this->outputFileInfo($outputPath);
    }

    private function generateHeader(string $title): string
    {
        return <<<SQL
-- =============================================================================
-- {$title}
-- =============================================================================


SQL;
    }

    private function exportMysqlConnection(string $connection, string $alias): string
    {
        $this->info("  処理中: {$alias} ({$connection}) ...");

        $output = $this->generateSectionHeader($alias, $connection);

        try {
            $tables = DB::connection($connection)
                ->select('SHOW TABLES');

            if (empty($tables)) {
                $this->warn("    警告: テーブルが見つかりません");
                return $output . "-- テーブルが見つかりません\n\n";
            }

            $tableCount = 0;
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];

                // 除外テーブルをスキップ
                if (in_array($tableName, self::EXCLUDED_TABLES, true)) {
                    continue;
                }

                $ddl = $this->getCreateTableStatement($connection, $tableName);
                if ($ddl) {
                    $output .= "-- Table: {$tableName}\n";
                    $output .= "{$ddl};\n\n";
                    $tableCount++;
                }
            }

            $this->info("    完了: {$tableCount} テーブル");
        } catch (\Exception $e) {
            $this->error("    エラー: " . $e->getMessage());
            $output .= "-- エラー: " . $e->getMessage() . "\n\n";
        }

        return $output;
    }

    private function exportTidbTables(string $connection, string $prefix, string $alias): string
    {
        // 出力ファイルには環境非依存の固定値を使用（実際の接続先は config から取得される）
        $this->info("  処理中: {$alias} (TiDB: local, prefix: {$prefix}) ...");

        $output = $this->generateSectionHeader($alias, "TiDB: local, prefix: {$prefix}");

        try {
            $tables = DB::connection($connection)
                ->select("SHOW TABLES LIKE ?", ["{$prefix}%"]);

            if (empty($tables)) {
                $this->warn("    警告: テーブルが見つかりません");
                return $output . "-- テーブルが見つかりません\n\n";
            }

            $tableCount = 0;
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];

                // 除外テーブルをスキップ
                if (in_array($tableName, self::EXCLUDED_TABLES, true)) {
                    continue;
                }

                $ddl = $this->getCreateTableStatement($connection, $tableName);
                if ($ddl) {
                    $output .= "-- Table: {$tableName}\n";
                    $output .= "{$ddl};\n\n";
                    $tableCount++;
                }
            }

            $this->info("    完了: {$tableCount} テーブル");
        } catch (\Exception $e) {
            $this->error("    エラー: " . $e->getMessage());
            $output .= "-- エラー: " . $e->getMessage() . "\n\n";
        }

        return $output;
    }

    private function generateSectionHeader(string $alias, string $detail): string
    {
        return <<<SQL

-- =============================================================================
-- Database: {$alias} ({$detail})
-- =============================================================================

SQL;
    }

    private function getCreateTableStatement(string $connection, string $tableName): ?string
    {
        try {
            $result = DB::connection($connection)
                ->select("SHOW CREATE TABLE `{$tableName}`");

            if (!empty($result)) {
                return $result[0]->{'Create Table'} ?? null;
            }
        } catch (\Exception $e) {
            $this->warn("      テーブル {$tableName} の取得に失敗: " . $e->getMessage());
        }

        return null;
    }

    private function outputFileInfo(string $outputPath): void
    {
        $fileSize = $this->formatFileSize(filesize($outputPath));
        $lineCount = count(file($outputPath));
        $fileName = basename($outputPath);
        $this->info("  → {$fileName} ({$fileSize}, {$lineCount}行)");
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        $size = (float)$bytes;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
