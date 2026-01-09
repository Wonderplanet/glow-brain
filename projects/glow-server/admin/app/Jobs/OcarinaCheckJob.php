<?php

namespace App\Jobs;

use App\Constants\OcarinaConstants;
use App\Services\AdminCacheService;
use App\Services\SlackService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;

class OcarinaCheckJob extends BaseJob
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    private string $dbName;
    private string $tableName;
    private array $sqlGroup;
    private AdminCacheService $adminCacheService;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $dbName,
        string $tableName,
        array $sqlGroup,
        AdminCacheService $adminCacheService
    ) {
        $this->dbName = $dbName;
        $this->tableName = $tableName;
        $this->sqlGroup = $sqlGroup;
        $this->adminCacheService = $adminCacheService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $database = new MasterDataDBOperator();
        $database->setConnection($this->dbName);
        $groupResult = [];

        foreach($this->sqlGroup as $sqlDate) {
            try {
                $result = DB::connection($this->dbName)->select($sqlDate['sql']);
                if(count($result) > 0) {
                    $groupResult[] = [$sqlDate['sql'], $result, $sqlDate['message']];
                }
            } catch (\Exception $e) {
                \Log::error($e);
                SlackService::send(
                    "**整合性チェック - クエリ実行失敗**\n"
                    . "環境: `" . config('app.env') . "`\n"
                    . "DB: `{$this->dbName}`\n"
                    . "テーブル: `{$this->tableName}`\n"
                    . "```\n"
                    . "[ 実行クエリ ]\n" . $sqlDate['sql'] . "\n\n"
                    . "[ チェック内容 ]\n" . $sqlDate['message'] . "\n"
                    . "```"
                );
            }
        }

        // 1件でもエラーがあれば通知
        if (count($groupResult) > 0) {
            $this->adminCacheService->putOcarinaError($this->dbName, true);

            // Slack通知
            $message = "**整合性チェックエラー検出**\n"
                . "環境: `" . config('app.env') . "`\n"
                . "DB: `{$this->dbName}`\n"
                . "テーブル: `{$this->tableName}`\n\n";
            foreach ($groupResult as $result) {
                $message .= "```";
                $message .= "\n[ チェック内容 ]\n" . $result[2] . "\n";
                $message .= "\n[ 実行クエリ ]\n" . $result[0] . "\n\n[ 不整合データ ]\n";
                foreach ($result[1] as $row) {
                    $message .= json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
                }
                $message .= "```\n";
            }

            SlackService::send($message);
        }
    }
}
