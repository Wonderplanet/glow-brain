<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Delegators;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use WonderPlanet\Domain\Currency\Entities\ForeignCurrencyMonthlyRateEntity;
use WonderPlanet\Domain\Currency\Entities\ScrapeForeignCurrencyDailyRateResultEntity;
use WonderPlanet\Domain\Currency\Entities\ScrapeForeignCurrencyRateResultEntity;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencyFreeEntity;
use WonderPlanet\Domain\Currency\Entities\UsrCurrencySummaryEntity;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;
use WonderPlanet\Domain\Currency\Models\MstStoreProduct;
use WonderPlanet\Domain\Currency\Models\OprProduct;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyPaid;
use WonderPlanet\Domain\Currency\Services\BulkCurrencyRevertTaskService;
use WonderPlanet\Domain\Currency\Services\CurrencyAdminService;
use WonderPlanet\Domain\Currency\Services\CurrencyService;
use WonderPlanet\Domain\Currency\Utils\Csv\BulkLogCurrencyRevertSearch;
use WonderPlanet\Domain\Currency\Utils\Excel\CollaboAggregation;
use WonderPlanet\Domain\Currency\Utils\Excel\CurrencyBalanceMultipleSheets;

/**
 * 管理画面向けのDelegator
 */
class CurrencyAdminDelegator
{
    public function __construct(
        private CurrencyService $currencyService,
        private CurrencyAdminService $currencyAdminService,
        private BulkCurrencyRevertTaskService $bulkCurrencyRevertTaskService
    ) {
    }

    /**
     * 有償一次通貨を追加する
     *
     * currency_summaryの更新も行う
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $currencyCode
     * @param string $purchasePrice
     * @param int $purchaseAmount
     * @param int $vipPoint
     * @param string $receiptUniqueId
     * @param Trigger $trigger
     * @return UsrCurrencyPaid
     */
    public function addSandboxCurrencyPaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $currencyCode,
        string $purchasePrice,
        int $purchaseAmount,
        int $vipPoint,
        string $receiptUniqueId,
        Trigger $trigger
    ): UsrCurrencyPaid {
        return $this->currencyService->addCurrencyPaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $purchaseAmount,
            $currencyCode,
            $purchasePrice,
            $vipPoint,
            $receiptUniqueId,
            true,
            $trigger
        );
    }

    /**
     * @param string $userId
     * @return UsrCurrencySummaryEntity|null
     */
    public function getCurrencySummary(string $userId): UsrCurrencySummaryEntity|null
    {
        return $this->currencyService->getCurrencySummary($userId);
    }

    /**
     * 指定されたログの内容を元に、通貨を補填する
     *
     * @param string $userId
     * @param array<string> $logCurrencyPaidIds
     * @param array<string> $logCurrencyFreeIds
     * @param string $comment
     * @param int $revertCount
     *
     * @return array<string> 返却したLogCurrencyRevertHistoryのID
     */
    public function revertCurrencyFromLog(
        string $userId,
        array $logCurrencyPaidIds,
        array $logCurrencyFreeIds,
        string $comment,
        int $revertCount,
    ): array {
        return $this->currencyAdminService->revertCurrencyFromLog(
            $userId,
            $logCurrencyPaidIds,
            $logCurrencyFreeIds,
            $comment,
            $revertCount,
        );
    }

    /**
     * 有償・無償ログの初回レコードから年の選択オプションを生成して取得
     *
     * @return string[]
     */
    public function getYearOptions(): array
    {
        return $this->currencyAdminService->getYearOptions();
    }

    /**
     * 一次通貨残高集計エクセルバイナリファイルを取得
     *
     * @param string $year
     * @param string $month
     * @param bool $outputBalanceAggregationAll
     * @param bool $outputBalanceAggregationApple
     * @param bool $outputBalanceAggregationGoogle
     * @param bool $outputPaidDetailAll
     * @param bool $outputPaidDetailApple
     * @param bool $outputPaidDetailGoogle
     * @param bool $outputForeignCountry
     * @param bool $isIncludeSandbox
     * @return CurrencyBalanceMultipleSheets
     */
    public function makeExcelCurrencyBalanceAggregation(
        string $year,
        string $month,
        bool $outputBalanceAggregationAll,
        bool $outputBalanceAggregationApple,
        bool $outputBalanceAggregationGoogle,
        bool $outputPaidDetailAll,
        bool $outputPaidDetailApple,
        bool $outputPaidDetailGoogle,
        bool $outputForeignCountry,
        bool $isIncludeSandbox
    ): CurrencyBalanceMultipleSheets {
        return $this->currencyAdminService
            ->makeExcelCurrencyBalanceAggregation(
                $year,
                $month,
                $outputBalanceAggregationAll,
                $outputBalanceAggregationApple,
                $outputBalanceAggregationGoogle,
                $outputPaidDetailAll,
                $outputPaidDetailApple,
                $outputPaidDetailGoogle,
                $outputForeignCountry,
                $isIncludeSandbox
            );
    }

    /**
     * コラボ消費通貨の集計情報Excelを取得
     *
     * startAt、endAtに指定される値は
     * 最終的にutc()でuTC時刻を取得して検索条件とする。
     *
     * 入力時のタイムゾーンがずれないよう、入力時刻をJSTとする場合はJSTでオブジェクトを作成しておくこと
     *
     * @param Carbon $startAt
     * @param Carbon $endAt
     * @param array<array{type: string, ids: array<string>}> $searchTriggers
     * @param bool $isIncludeSandbox
     *
     * @return CollaboAggregation
     */
    public function makeExcelCollaboAggregation(
        Carbon $startAt,
        Carbon $endAt,
        array $searchTriggers,
        bool $isIncludeSandbox
    ): CollaboAggregation {
        return $this->currencyAdminService->makeExcelCollaboAggregation(
            $startAt,
            $endAt,
            $searchTriggers,
            $isIncludeSandbox
        );
    }

    /**
     * 一次通貨返却(一括)対象データ情報CSVを取得
     *
     * startAt、endAtに指定される値は
     * 最終的にutc()でuTC時刻を取得して検索条件とする。
     *
     * 入力時のタイムゾーンがずれないよう、入力時刻をJSTとする場合はJSTでオブジェクトを作成しておくこと
     * @param CarbonImmutable $startAt
     * @param CarbonImmutable $endAt
     * @param string $triggerType
     * @param string $triggerId
     * @param bool $isIncludeSandbox
     * @return BulkLogCurrencyRevertSearch
     */
    public function makeCsvBulkLogCurrencyRevertSearch(
        CarbonImmutable $startAt,
        CarbonImmutable $endAt,
        string $triggerType,
        string $triggerId,
        bool $isIncludeSandbox
    ): BulkLogCurrencyRevertSearch {
        return $this->currencyAdminService->makeCsvBulkLogCurrencyRevertSearch(
            $startAt,
            $endAt,
            $triggerType,
            $triggerId,
            $isIncludeSandbox
        );
    }

    /**
     * 対象年月日の外貨為替相場データを取得
     *
     * @param int $year
     * @param int $month
     * @return ForeignCurrencyMonthlyRateEntity
     */
    public function getForeignCurrencyMonthlyRate(int $year, int $month): ForeignCurrencyMonthlyRateEntity
    {
        return $this->currencyAdminService->getForeignCurrencyMonthlyRate($year, $month);
    }

    /**
     * 指定された年月のデータを削除する
     *
     * @param int $year
     * @param int $month
     */
    public function deleteForeignCurrencyRateByYearAndMonth(int $year, int $month): void
    {
        $this->currencyAdminService->deleteForeignCurrencyRateByYearAndMonth($year, $month);
    }

    /**
     * 対象年月の外貨為替相場データが取得済みか
     *
     * @param int $year
     * @param int $month
     * @return array<string,bool>
     */
    public function existsScrapeForeignCurrencyRateByYearAndMonth(int $year, int $month): array
    {
        return $this->currencyAdminService
            ->existsScrapeForeignCurrencyRateByYearAndMonth($year, $month);
    }

    /**
     * 外貨為替相場データの登録実行
     * 管理画面のログ集計処理内で外貨為替レート情報を使用する
     * その為コマンドとは別に、必要に応じて各ログ集計処理前に呼び出すこと
     *
     * 内部でエラーが発生した場合は、戻り値のEntityに格納する。
     * このメソッドで取得時の例外は発生させない
     *
     * @param int $year
     * @param int $month
     * @return ScrapeForeignCurrencyRateResultEntity
     */
    public function scrapeForeignCurrencyRate(int $year, int $month): ScrapeForeignCurrencyRateResultEntity
    {
        return $this->currencyAdminService
            ->scrapeForeignCurrencyRate($year, $month);
    }

    /**
     * 本日の為替相場データの登録実行
     *
     * @return ScrapeForeignCurrencyDailyRateResultEntity
     */
    public function scrapeForeignCurrencyDailyRate(): ScrapeForeignCurrencyDailyRateResultEntity
    {
        return $this->currencyAdminService->scrapeForeignCurrencyDailyRate();
    }

    /**
     * ユーザーの一次通貨消費を行う
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param int $amount
     * @param Trigger $trigger
     * @return void
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException
     */
    public function useCurrency(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): void {
        $this->currencyService->useCurrency(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            $trigger
        );
    }

    /**
     * ユーザーの有償一次通貨を消費する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param int $amount
     * @param Trigger $trigger
     * @return void
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyException
     */
    public function usePaid(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $amount,
        Trigger $trigger
    ): void {
        $this->currencyService->usePaid(
            $userId,
            $osPlatform,
            $billingPlatform,
            $amount,
            $trigger
        );
    }

    /**
     * ユーザーの無償一次通貨を回収する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $type
     * @param integer $amount
     * @param string $triggerDetail
     * @return UsrCurrencySummaryEntity
     */
    public function collectFreeCurrency(
        string $userId,
        string $osPlatform,
        string $type,
        int $amount,
        string $triggerDetail,
    ): UsrCurrencySummaryEntity {
        return $this->currencyAdminService->collectFreeCurrency(
            $userId,
            $osPlatform,
            $type,
            $amount,
            $triggerDetail
        );
    }

    /**
     * ユーザーの無償一次通貨を取得する
     *
     * @param string $userId
     * @return UsrCurrencyFreeEntity|null
     */
    public function getCurrencyFree(string $userId): ?UsrCurrencyFreeEntity
    {
        return $this->currencyService->getCurrencyFree($userId);
    }

    /**
     * @param string $id
     * @return OprProduct|null
     */
    public function getOprProductById(string $id): ?OprProduct
    {
        return $this->currencyAdminService
            ->getOprProductById($id);
    }

    /**
     * @param string $id
     * @return MstStoreProduct|null
     */
    public function getMstStoreProductById(string $id): ?MstStoreProduct
    {
        return $this->currencyAdminService
            ->getMstStoreProductById($id);
    }

    /**
     * log_currency_paidsとlog_currency_freesをunionした消費ログを取得する
     *
     * @param string|null $startAtUtc
     * @param string|null $endAtUtc
     * @param string|null $triggerType
     * @param string|null $triggerId
     * @param string|null $userId
     * @param bool $isIncludeSandbox
     *
     * @return Builder<\WonderPlanet\Domain\Currency\Models\LogCurrencyUnionModel>
     */
    public function getConsumeLogCurrencyPaidAndFrees(
        ?string $startAtUtc,
        ?string $endAtUtc,
        ?string $triggerType,
        ?string $triggerId,
        ?string $userId,
        bool $isIncludeSandbox,
    ): Builder {
        return $this->currencyAdminService->getConsumeLogCurrencyPaidAndFrees(
            $startAtUtc,
            $endAtUtc,
            $triggerType,
            $triggerId,
            $userId,
            $isIncludeSandbox,
        );
    }

    // 一次通貨返却一括実行
    /**
     * 一括通貨返却タスクを登録する
     *
     * 処理単位のタスクのみ登録する。
     * 処理するターゲットは別メソッドで登録する
     *
     * @param integer $admUserId
     * @param string $fileName
     * @param integer $revertCurrencyNum
     * @param string $comment
     * @param integer $totalCount
     *
     * @return AdmBulkCurrencyRevertTask
     */
    public function registerBulkCurrencyRevertTask(
        int $admUserId,
        string $fileName,
        int $revertCurrencyNum,
        string $comment,
        int $totalCount,
    ): AdmBulkCurrencyRevertTask {
        return $this->bulkCurrencyRevertTaskService->registerTask(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            $totalCount,
        );
    }

    /**
     * 一括通貨返却タスクの処理対象を登録する
     *
     * $dataBodyはキーと値の構造になっている
     * 読み込まれたデータファイルはデータのみになっているため、
     * メソッド呼び出し側でキーにマッピングされた状態で渡されることを想定している
     *
     * @param string $bulkCurrencyRevertTaskId
     * @param integer $revertCurrencyNum
     * @param string $comment
     * @param array<mixed> $dataBody 通貨返却対象データ
     * @param integer $chunkSize
     *
     * @codingStandardsIgnoreStart
     * @return \Illuminate\Database\Eloquent\Collection<int, \WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget> 登録したタスクと対象ユーザーリスト
     * @codingStandardsIgnoreEnd
     */
    public function registerBulkCurrencyRevertTaskTargets(
        string $bulkCurrencyRevertTaskId,
        int $revertCurrencyNum,
        string $comment,
        array $dataBody,
        int $chunkSize = 1000
    ): EloquentCollection {
        return $this->bulkCurrencyRevertTaskService->registerTaskTargets(
            $bulkCurrencyRevertTaskId,
            $revertCurrencyNum,
            $comment,
            $dataBody,
            $chunkSize
        );
    }

    /**
     * 対象データに対して通貨を返却する
     *
     * 返却した後で、関連して廃部処理を行う場合などは$afterRevertCallbackを設定します。
     * $afterRevertCallbackメソッドが動作し終わるとステータスがFinishedに変更されます。
     *
     * 配布完了後のメッセージ送信など、完全に配布が終わってからの処理は$finishedCallbackを設定します。
     *
     * エラー時には$errorCallbackが実行され、ステータスがErrorに変更されます。
     *
     * @param AdmBulkCurrencyRevertTaskTarget $target
     * @param integer $revertCurrencyNum
     * @param string $comment
     * @param Closure|null $beforeTargetRevertCallback 通貨返却前に実行する処理
     * @param Closure|null $afterTargetRevertCallback 通貨返却後に実行する処理
     * @param Closure|null $finishedTargetCallback 通貨返却処理が完了した際に実行する処理
     * @param Closure|null $errorTargetCallback 通貨返却処理がエラーになった際に実行する処理
     * @return array<string> 返却したLogCurrencyRevertHistoryのID
     */
    public function revertCurrencyFromBulkCurrencyRevertTaskTarget(
        AdmBulkCurrencyRevertTaskTarget $target,
        int $revertCurrencyNum,
        string $comment,
        ?Closure $beforeTargetRevertCallback = null,
        ?Closure $afterTargetRevertCallback = null,
        ?Closure $finishedTargetCallback = null,
        ?Closure $errorTargetCallback = null,
    ): array {
        return $this->bulkCurrencyRevertTaskService->revertCurrencyFromTarget(
            $target,
            $revertCurrencyNum,
            $comment,
            $beforeTargetRevertCallback,
            $afterTargetRevertCallback,
            $finishedTargetCallback,
            $errorTargetCallback
        );
    }

    /**
     * 一次通貨返却(一括)タスクを終了する
     *
     * @param string $taskId
     * @return void
     */
    public function finishBulkCurrencyRevertTask(string $taskId): void
    {
        $this->bulkCurrencyRevertTaskService->finishBulkCurrencyRevertTask($taskId);
    }

    /**
     * タスクをエラーにする
     *
     * @param string $taskId
     * @param \Throwable $error
     * @return void
     */
    public function updateBulkCurrencyRevertTaskToError(string $taskId, \Throwable $error): void
    {
        $this->bulkCurrencyRevertTaskService->updateTaskToError($taskId, $error);
    }

    /**
     * 対象データをエラーにする
     *
     * @param string $targetId
     * @param \Throwable $error
     * @return void
     */
    public function updateBulkCurrencyRevertTaskTargetToError(string $targetId, \Throwable $error): void
    {
        $this->bulkCurrencyRevertTaskService->updateTargetToError($targetId, $error);
    }
}
