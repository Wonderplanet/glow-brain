<?php

namespace App\Services;

use App\Constants\OcarinaConstants;
use App\Jobs\OcarinaCheckJob;
use App\Models\Adm\AdmUser;
use App\Services\AdminCacheService;
use App\Services\SlackService;
use Filament\Notifications\Notification;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Bus;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\GoogleSpreadSheetOperator;

class OcarinaService
{
    public function __construct(
        private AdminCacheService $adminCacheService
    ) {
    }

    /**
     * 整合性チェックを実行する
     *
     * @param string $dbName
     * @param AdmUser $user
     * @return void
     * @throws \Exception
     */
    public function executeIntegrityCheck(string $dbName, AdmUser $user): void
    {
        if (! env('OCARINA_ACTIVE', false)){
            return;
        }

        $env = config('app.env');
        
        // 整合性チェックを実行することをslackに通知
        SlackService::send("`{$env}環境`の整合性チェックを開始" . $user->getMention());

        // 整合性チェック->ロールバックor完了処理
        $jobs = $this->createCheckJobs($dbName);

        if (count($jobs) <= 0) {
            // slack通知
            $mention = $user->getMention();
            $message = "`{$env}環境`の整合性チェック：チェック対象データがありませんでした。";
            SlackService::send($message . $mention);
            return;
        }

        $batch = Bus::batch($jobs)->allowFailures()->dispatch();
        while (!$batch->finished()) {
            $batch = $batch->fresh();
            sleep(1);
        }

        // Jobの中で整合性チェックをした結果不整合があったか
        $ocarinaError = $this->adminCacheService->getOcarinaError($dbName);

        if ($ocarinaError) {
            // slack通知
            $mention = $user->getMention();
            $message = "`{$env}環境`の整合性チェックエラー：修正＆再度取り込みを行って下さい。";
            SlackService::send($message . $mention);
            Notification::make()
                ->title('整合性チェックでエラーが発生しました。Slack:glow_ocarina-wpを確認し。修正＆再度取り込みを行って下さい。')
                ->danger()
                ->send();
        } else {
            SlackService::send(
                "`{$env}環境`の整合性チェックが完了しました。実行者: " . $user->getMention()
            );
        }
        
        // キャッシュの削除
        $this->adminCacheService->deleteOcarinaError($dbName);
    }

    private function createCheckJobs(string $dbName): array
    {
        // 各チェックデータ生成
        try {
            $sheets = $this->getOcarinaSheets();
            $s = $this->sqlCheck($sheets[0]);
            $r = $this->referenceCheck($sheets[1]);
            $d = $this->duplicationCheck($sheets[2]);

            $keys =  array_merge(array_keys($s), array_keys($d), array_keys($r));
            $sql = [];
            foreach ($keys as $key) {
                $sData = $s[$key] ?? [];
                $dData = $d[$key] ?? [];
                $rData = $r[$key] ?? [];
                $sql[$key] = array_merge($sData, $dData, $rData);
            }

            $jobs = [];
            foreach ($sql as $tableName => $sqlGroup) {
                $jobs[] = new OcarinaCheckJob(
                    $dbName,
                    $tableName,
                    $sqlGroup,
                    $this->adminCacheService
                );
            }

            // チェックデータを返す
            return $jobs;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getOcarinaSheets(): array
    {
        $googleSpreadSheetOperator = new GoogleSpreadSheetOperator(config('admin.googleCredentialPath'));

        $client = new \Google_Client();
        $client->setAuthConfig(config('admin.googleCredentialPath'));
        $client->setApplicationName("Test"); // 適当な名前でOK
        $client->addScope(Sheets::SPREADSHEETS);
        $service = new Sheets($client);

        $spreadsheets = $service->spreadsheets->get(OcarinaConstants::OCARINA_SHEET_ID);
        $sheets = [];

        foreach ($spreadsheets as $spreadsheet) {
            $sheets[] = $googleSpreadSheetOperator->getSheetValues(OcarinaConstants::OCARINA_SHEET_ID, $spreadsheet->properties->title);
        }

        return $sheets;
    }

    private function sqlCheck(array $sheetData): array
    {
        $sqlTableGroup = [];
        $sheetData = array_merge(array_filter($sheetData));
        for ($i = 2; $i < count($sheetData); $i++) {
            $sheet = array_merge(array_filter($sheetData[$i]));
            if ($sheet[0] != 'e') {
                continue;
            }
            $sqlTableGroup[$sheet[2]][] = [
                'message' => $sheet[3],
                'sql' => $sheet[4],
                'column' => null,
            ];
        }
        return $sqlTableGroup;
    }

    private function referenceCheck(array $sheetData): array
    {
        $sqlTableGroup = [];
        $sheetData = array_merge(array_filter($sheetData));
        for ($i = 0; $i < count($sheetData); $i++) {
            $sheet = array_merge(array_filter($sheetData[$i]));
            if ($sheet[0] != 'e') {
                continue;
            }
            $master = $sheet[1];
            $notifyColumn = $sheet[2];
            $targetColumn = $sheet[3];
            $refMaster = $sheet[4];
            $refColumn = $sheet[5];

            $notify_column=explode(",", $notifyColumn);
            $select_column_base='';
            foreach ($notify_column as $column) {
                if (empty($select_column_base)) {
                    $select_column_base = "a.$column";
                } else {
                    $select_column_base .= ", a.$column";
                }
            }
            $target_column = explode("，", $targetColumn);
            $ref_column = explode("，", $refColumn);

            # クエリに必要なパラメータの準備
            $select_column='';
            $join_query='';
            $null_column='';
            $zero_check='';
            for ($j = 0; $j < count($target_column); $j++) {
                if (empty($select_column)) {
                    $select_column = "$select_column_base, a.$target_column[$j]";
                    $join_query = "a.$target_column[$j] = b.$ref_column[$j]";
                    $null_column = "b.$ref_column[$j]";
                    $zero_check = "a.$target_column[$j] <> ''";
                } else {
                    $select_column .= ", a.$target_column[$j]";
                    $join_query .= " AND a.$target_column[$j] = b.$ref_column[$j]";
                    $zero_check .= " AND a.$target_column[$j] <> ''";
                }
            }

            $tableName = $master;
            $refTableName = $refMaster;
            $query = "SELECT $select_column FROM $tableName AS a LEFT JOIN $refTableName AS b ON $join_query WHERE $null_column IS NULL AND $zero_check;";

            $sqlTableGroup[$master][] = [
                'message' => "$master:[$targetColumn] に $refMaster:[$refColumn] に紐づかない値があります",
                'sql' => $query,
                'column' => $notifyColumn,
            ];
        }
        return $sqlTableGroup;
    }

    private function duplicationCheck(array $sheetData): array
    {
        $sqlTableGroup = [];
        $sheetData = array_merge(array_filter($sheetData));
        for ($i = 0; $i < count($sheetData); $i++) {
            $sheet = array_merge(array_filter($sheetData[$i]));
            if ($sheet[0] != 'e') {
                continue;
            }
            $master = $sheet[1];
            $targetColumn = $sheet[2];

            $tableName = $master;
            $query = "SELECT $tableName.$targetColumn FROM $tableName GROUP BY $tableName.$targetColumn HAVING COUNT(*) > 1;";
            $sqlTableGroup[$sheet[1]][] = [
                'message' => " $master の $targetColumn に重複があります",
                'sql' => $query,
                'column' => $targetColumn,
            ];
        }
        return $sqlTableGroup;
    }
}
