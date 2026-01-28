<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportApiPerformance extends Command
{
    protected $signature = 'export:api-performance';
    protected $description = 'Export API performance and DB access statistics to CSV';

    public function handle()
    {
        $filePath = storage_path('app/api_performance.csv');
        $dbQueryFilePath = storage_path('app/db_query.csv');

        // ファイルを開く（なければ作成）
        $fileHandle = fopen($filePath, 'w');
        $dbQueryFileHandle = fopen($dbQueryFilePath, 'w');

        // ヘッダー行を書き込み
        fputcsv($fileHandle, ['API Path', 'Average Time (ms)', 'usr_ Table Accesses', 'log_ Table Accesses', 'mst_ Table Accesses', 'usr_mission_ Table Accesses']);
        fputcsv($dbQueryFileHandle, ['API Path', 'Table Name', 'Access Count']);

        // APIリクエストのエントリーをテキスト形式で取得し、後で処理する
        $apiLogs = DB::table('telescope_entries')
            ->where('type', 'request')
            ->get();

        $queryLogs = DB::table('telescope_entries')
            ->where('type', 'query')
            ->get();

        $batchIdSqlsMap = $queryLogs->groupBy('batch_id')->map(function ($logs) {
            return $logs->map(function ($log) {
                return json_decode($log->content)->sql;
            })->filter(function ($sql) {
                return !str_contains($sql, 'telescope');
            });
        });

        // APIパスごとに集計
        $apiData = [];
        $dbQueryData = [];
        foreach ($apiLogs as $log) {
            // content列をテキスト形式で取得し、デコードして配列として処理
            $content = json_decode($log->content, true);
            $uri = $content['uri'] ?? '';
            $duration = $content['duration'] ?? 0;

            // APIパスごとにデータを集計
            if (!isset($apiData[$uri])) {
                $apiData[$uri] = [
                    'total_duration' => 0,
                    'request_count' => 0,
                    'usr_count' => 0,
                    'log_count' => 0,
                    'mst_count' => 0,
                    'usr_mission_count' => 0,
                ];
            }

            // 合計時間とリクエスト数をカウント
            $apiData[$uri]['total_duration'] += $duration;
            $apiData[$uri]['request_count']++;

            // データベースアクセスを集計
            $sqls = $batchIdSqlsMap->get($log->batch_id);
            if (is_null($sqls)) {
                continue;
            }

            $apiData[$uri]['usr_count'] += $this->countTableAccesses($sqls, 'usr_');
            $apiData[$uri]['log_count'] += $this->countTableAccesses($sqls, 'log_');
            $apiData[$uri]['mst_count'] += $this->countTableAccesses($sqls, 'mst_');
            $apiData[$uri]['usr_mission_count'] += $this->countTableAccesses($sqls, 'usr_mission_');

            // テーブルアクセス数を集計
            // key1: uri, key2: table name, value: counts
            foreach ($sqls as $sql) {
                $tableName = $this->extractTableNameFromSql($sql);
                if (is_null($tableName)) {
                    continue;
                }

                if (!isset($dbQueryData[$uri])) {
                    $dbQueryData[$uri] = [];
                }
                if (!isset($dbQueryData[$uri][$tableName])) {
                    $dbQueryData[$uri][$tableName] = 0;
                }
                $dbQueryData[$uri][$tableName]++;
            }
        }

        // 集計結果をCSVに書き込み
        foreach ($apiData as $uri => $data) {
            $count = $data['request_count'];

            $avgDuration = $count > 0 ? round($data['total_duration'] / $count, 2) : 0;
            $usrCount =  $count > 0 ? round($data['usr_count'] / $count, 2) : 0;
            $logCount =  $count > 0 ? round($data['log_count'] / $count, 2) : 0;
            $mstCount =  $count > 0 ? round($data['mst_count'] / $count, 2) : 0;
            $usrMissionCount =  $count > 0 ? round($data['usr_mission_count'] / $count, 2) : 0;

            fputcsv($fileHandle, [
                $uri,
                $avgDuration,
                $usrCount,
                $logCount,
                $mstCount,
                $usrMissionCount,
            ]);

            // テーブルアクセス数平均を書き込み
            $dbQueryDataByUri = $dbQueryData[$uri] ?? [];
            foreach ($dbQueryDataByUri as $tableName => $accessCount) {
                fputcsv($dbQueryFileHandle, [$uri, strtolower($tableName), round($accessCount / $count, 2)]);
            }
        }

        // ファイルを閉じる
        fclose($fileHandle);
        fclose($dbQueryFileHandle);

        $this->info("API performance data exported to {$filePath}");
        $this->info("DB access statistics exported to {$dbQueryFilePath}");
    }

    protected function countTableAccesses($sqls, $tablePrefix)
    {
        $count = 0;

        // 各SQLクエリに対して処理を行う
        foreach ($sqls as $sql) {
            $tableName = $this->extractTableNameFromSql($sql);
            if (is_null($tableName)) {
                continue;
            }

            // テーブル名が指定されたプレフィックスで始まるかを確認
            if (strpos($tableName, strtoupper($tablePrefix)) === 0) {
                $count++;
            }
        }

        return $count;
    }

    protected function extractTableNameFromSql($sql)
    {
        // クエリを大文字に変換して解析しやすくする
        $upperSql = strtoupper($sql);

        // クエリをスペースで分割
        $sqlParts = explode(' ', $upperSql);

        // INSERT, UPDATE, DELETE 文の場合は次のフォーマットを仮定して解析
        // INSERT INTO table_name ...
        // UPDATE table_name ...
        // DELETE FROM table_name ...
        if (in_array($sqlParts[0], ['INSERT', 'UPDATE', 'DELETE'])) {
            // INSERTの場合、テーブル名は[2]番目
            // UPDATEとDELETEの場合、テーブル名は[1]番目
            $tableName = ($sqlParts[0] === 'INSERT') ? $sqlParts[2] : $sqlParts[1];
        }
        // SELECT 文の場合は、FROMの次に来るテーブル名を取得
        elseif ($sqlParts[0] === 'SELECT') {
            $fromIndex = array_search('FROM', $sqlParts);
            if ($fromIndex !== false && isset($sqlParts[$fromIndex + 1])) {
                $tableName = $sqlParts[$fromIndex + 1];
            } else {
                return null; // FROMの次にテーブル名が見つからない場合はスキップ
            }
        } else {
            return null; // その他のSQL文は無視
        }

        return str_replace('`', '', $tableName);
    }
}
