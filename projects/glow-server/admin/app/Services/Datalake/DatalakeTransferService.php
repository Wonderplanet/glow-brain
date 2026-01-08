<?php

declare(strict_types=1);

namespace App\Services\Datalake;

use App\Constants\DatalakeConstant;
use App\Constants\DatalakeStatus;
use App\Models\GenericLogModel;
use App\Models\GenericMstModel;
use App\Models\GenericOprModel;
use App\Models\GenericUsrModel;
use App\Repositories\Adm\AdmDatalakeLogRepository;
use App\Services\Datalake\TidbDumpService;
use App\Traits\DatabaseTransactionTrait;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

/**
 * データレイク転送サービス
 *  基本転送機構と再送機構で使用する
 */
class DatalakeTransferService
{
    use DatabaseTransactionTrait;

    public function __construct(
        private DatalakeService $datalakeService,
        private AdmDatalakeLogRepository $admDatalakeLogRepository,
        private TidbDumpService $tidbDumpService,
    ) { }

    public function execTransfer(string $env, CarbonImmutable $targetDate): void
    {
        Log::info("データレイク転送:開始");
        $dateNum = (int)$targetDate->format('Ymd');
        $admDatalakeLog = $this->admDatalakeLogRepository->getByDate($dateNum);
        if ($admDatalakeLog === null) {
            $admDatalakeLog = $this->admDatalakeLogRepository->createModel($dateNum);
        }

        // この段階で転送実行中の場合か、全ての処理が完了している場合は、何もしない
        if ($admDatalakeLog->getIsTransfer() || $admDatalakeLog->getStatus() === DatalakeStatus::COMPLETED->value) {
            Log::info("データレイク転送:終了:{$admDatalakeLog->getIsTransfer()}:{$admDatalakeLog->getStatus()}");
            return;
        }

        // 転送中に変更
        $result = $this->transaction(
            function () use ($dateNum, &$admDatalakeLog): bool {
                $admDatalakeLog = $this->admDatalakeLogRepository->getByDate($dateNum);
                if ($admDatalakeLog === null) {
                    $admDatalakeLog = $this->admDatalakeLogRepository->createModel($dateNum);
                }
                // この段階で転送実行中の場合は、何もせず終了
                if ($admDatalakeLog->getIsTransfer()) {
                    Log::info("データレイク転送:前回ログが転送中の為、終了");
                    return false;
                }
                $admDatalakeLog->setIsTransfer(true);
                $admDatalakeLog->setTryCount($admDatalakeLog->getTryCount() + 1);
                $admDatalakeLog->save();
                Log::info("データレイク転送:転送中に変更");
                return true;
            }
        );
        if (!$result) {
            return;
        }

        // 高負荷で長時間の処理の為、段階的に転送処理を行い、完了次第進捗を保存する
        // json変換中はtransactionを行わず、転送時にtransactionと進捗更新を行う
        do {
            // 現在のステータスから次のステータスを取得
            $nowStatus = DatalakeStatus::from($admDatalakeLog->getStatus());
            $nextStatus = $nowStatus->getNextStatus();
            // 次のステータスがCOMPLETEDの場合は、全ての処理が完了しているので終了
            if ($nextStatus === DatalakeStatus::COMPLETED) {
                break;
            }

            // 次のステータスに応じて、JSONファイルリスト、dbタイプ、接頭辞ベース文字列、db名を取得
            list($fileNameList, $dbType, $prefixBase, $dbName) = match($nextStatus) {
                DatalakeStatus::MST_DB_TRANSFERRED => [
                    $this->datalakeService->outputMstDBForJson($targetDate),
                    'mst',
                    DatalakeConstant::GCS_PREFIX_MST,
                    (new GenericMstModel())->getDBName(),
                ],
                DatalakeStatus::OPR_DB_TRANSFERRED => [
                    $this->datalakeService->outputOprDBForJson($targetDate),
                    'opr',
                    DatalakeConstant::GCS_PREFIX_OPR,
                    (new GenericOprModel())->getDBName(),
                ],
                DatalakeStatus::USR_DB_TRANSFERRED => [
                    // TidbDumpServiceで直接dump→JSON変換→GCS転送まで実行（戻り値は転送済みファイル名リスト）
                    $this->tidbDumpService->dumpTidbTablesToJson('usr', $targetDate, DatalakeConstant::DISK_TEMP),
                    'usr',
                    DatalakeConstant::GCS_PREFIX_USR,
                    (new GenericUsrModel())->getDBName(),
                ],
                DatalakeStatus::LOG_DB_TRANSFERRED => [
                    // TidbDumpServiceで直接dump→JSON変換→GCS転送まで実行（戻り値は転送済みファイル名リスト）
                    $this->tidbDumpService->dumpTidbTablesToJson('log', $targetDate, DatalakeConstant::DISK_TEMP),
                    'log',
                    DatalakeConstant::GCS_PREFIX_LOG,
                    (new GenericLogModel())->getDBName(),
                ],
                default => throw new \Exception('想定外の転送ステータス'),
            };
            $dbName = $this->filterDbName($dbType, $dbName);
            $pathPrefix = $this->getPathPrefix($prefixBase, $env, $dbName);

            // usr/logの場合はTidbDumpServiceで既にGCS転送まで完了しているため転送処理をスキップ
            if ($dbType === 'usr' || $dbType === 'log') {
                Log::info("データレイク転送:{$dbType}処理完了 ({$fileNameList->count()}ファイル転送済み)");
                $result = $this->transaction(
                    function () use ($nextStatus, &$admDatalakeLog) {
                        $admDatalakeLog->setStatus($nextStatus->value);
                        $admDatalakeLog->save();
                        return true;
                    }
                );
            } else {
                // mst/oprの場合は従来通りGCS転送を実行
                Log::info("データレイク転送:{$dbType}JSONファイル転送開始:{$fileNameList->count()}ファイル");
                $result = $this->transaction(
                    function () use ($nextStatus, $pathPrefix, $targetDate, $fileNameList, &$admDatalakeLog) {
                        $result = $this->datalakeService->compressAndUploadToGcsWithCompression(
                            $pathPrefix,
                            $targetDate,
                            $fileNameList,
                            DatalakeConstant::DISK_TEMP,
                            DatalakeConstant::DISK_GCS,
                        );

                        // 転送が失敗したらステータス変更せず強制終了
                        if (!$result) {
                            Log::error("データレイク転送:失敗");
                            return false;
                        }
                        $admDatalakeLog->setStatus($nextStatus->value);
                        $admDatalakeLog->save();
                        return true;
                    }
                );
            }
        } while ($result);

        $this->transaction(
            function () use ($targetDate, &$admDatalakeLog) {
                $nowStatus = DatalakeStatus::from($admDatalakeLog->getStatus());
                $nextStatus = $nowStatus->getNextStatus();
                // この時点で次のステータスがCOMPLETEDの場合は、全ての処理が完了しているので終了
                if ($nextStatus === DatalakeStatus::COMPLETED) {
                    $admDatalakeLog->setStatus($nextStatus->value);
                    $admDatalakeLog->setIsTransfer(false);
                    $admDatalakeLog->save();
                    Log::info("データレイク転送:完了");
                    return;
                }
                // ここまできたら、結果問わず転送終了
                if ($admDatalakeLog->getIsTransfer()) {
                    $admDatalakeLog->setIsTransfer(false);
                    $admDatalakeLog->save();
                    Log::warning("データレイク転送:未完了のまま終了:{$admDatalakeLog->getTryCount()}");
                }
            }
        );
    }

    private function getPathPrefix(string $prefixBase, string $env, string $dbName): string
    {
        $test = '';
        if ($env === 'local') {
            $test = 'test_';
        }
        return sprintf(
            $test . $prefixBase,
            $dbName,
            $env,
        );
    }

    private function filterDbName(string $dbType, string $dbName): string
    {
        // mstDBとoprDBは、ハッシュ値がついてる部分を削除する
        if ($dbType === 'mst' || $dbType === 'opr') {
            return preg_replace('/_[0-9]+_[a-f0-9]{32}$/', '', $dbName);
        }
        return $dbName;
    }
}
