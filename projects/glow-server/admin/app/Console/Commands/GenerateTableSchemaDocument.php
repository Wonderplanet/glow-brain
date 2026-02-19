<?php

namespace App\Console\Commands;

use App\Constants\DatalakeConstant;
use App\Entities\Clock;
use App\Exports\TableSchemaDocumentExport;
use App\Operators\S3Operator;
use App\Services\ConfigGetService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class GenerateTableSchemaDocument extends Command
{
    public function __construct(
        private Clock $clock,
        private S3Operator $s3Operator,
        private ConfigGetService $configGetService,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-table-schema-document';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $executionTime = $this->clock->now();
        $datetime = $executionTime->format('YmdHis');
        $filename = "table_schema_document_{$datetime}.xlsx";

        // まずローカルに一時保存
        $localPath = storage_path("app/{$filename}");
        Excel::store(new TableSchemaDocumentExport($executionTime), $filename, 'local');
        $this->info("File created locally: {$localPath}");

        // S3にアップロード
        try {
            $s3Config = $this->configGetService->getS3WpDatalake();
            $bucketName = $this->configGetService->getS3WpDatalakeBucket();

            if (empty($bucketName)) {
                throw new \Exception('WPデータレイク用S3バケットが設定されていません。AWS_WP_DATALAKE_BUCKETを設定してください。');
            }

            // データレイクのメタデータ領域にアップロード
            $s3Key = DatalakeConstant::WP_DATALAKE_S3_METADATA_SCHEMA_PREFIX . $filename;
            $this->s3Operator->putFromFileWithConfig($s3Config, $localPath, $s3Key);

            $this->info("File uploaded to S3: s3://{$bucketName}/{$s3Key}");

            // ローカルファイルを削除
            unlink($localPath);
            $this->info("Local file deleted.");

        } catch (\Exception $e) {
            $this->error("Failed to upload to S3: " . $e->getMessage());
            return 1;
        }

        $this->info("Export complete.");
        return 0;
    }
}
