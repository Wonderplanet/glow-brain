<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\LogCurrencyPaid;

class LogCurrencyPaidRepository
{
    /**
     * 有償一次通貨のログを追加する
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $seqNo
     * @param string $currencyPaidId
     * @param string $receiptUniqueId
     * @param boolean $isSandbox
     * @param string $query
     * @param string $purchasePrice
     * @param integer $purchaseAmount
     * @param string $pricePerAmount
     * @param integer $vipPoint
     * @param string $currencyCode
     * @param integer $beforeAmount
     * @param integer $changeAmount
     * @param integer $currentAmount
     * @param Trigger $trigger
     * @return string ログID
     */
    public function insertPaidLog(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        int $seqNo,
        string $currencyPaidId,
        string $receiptUniqueId,
        bool $isSandbox,
        string $query,
        string $purchasePrice,
        int $purchaseAmount,
        string $pricePerAmount,
        int $vipPoint,
        string $currencyCode,
        int $beforeAmount,
        int $changeAmount,
        int $currentAmount,
        Trigger $trigger
    ): string {
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();

        $logCurrencyPaid = new LogCurrencyPaid();

        $logCurrencyPaid->usr_user_id = $userId;
        $logCurrencyPaid->os_platform = $osPlatform;
        $logCurrencyPaid->billing_platform = $billingPlatform;
        $logCurrencyPaid->seq_no = $seqNo;
        $logCurrencyPaid->currency_paid_id = $currencyPaidId;
        $logCurrencyPaid->receipt_unique_id = $receiptUniqueId;
        $logCurrencyPaid->is_sandbox = (int)$isSandbox;
        $logCurrencyPaid->query = $query;
        $logCurrencyPaid->purchase_price = $purchasePrice;
        $logCurrencyPaid->purchase_amount = $purchaseAmount;
        $logCurrencyPaid->price_per_amount = $pricePerAmount;
        $logCurrencyPaid->vip_point = $vipPoint;
        $logCurrencyPaid->currency_code = $currencyCode;
        $logCurrencyPaid->before_amount = $beforeAmount;
        $logCurrencyPaid->change_amount = $changeAmount;
        $logCurrencyPaid->current_amount = $currentAmount;
        $logCurrencyPaid->trigger_type = $trigger->triggerType;
        $logCurrencyPaid->trigger_id = $trigger->triggerId;
        $logCurrencyPaid->trigger_name = $trigger->triggerName;
        $logCurrencyPaid->trigger_detail = $trigger->triggerDetail;
        $logCurrencyPaid->request_id_type = $requestUniqueIdData->getRequestIdType()->value;
        $logCurrencyPaid->request_id = $requestUniqueIdData->getRequestId();
        $logCurrencyPaid->nginx_request_id = CurrencyCommon::getFrontRequestId();
        $logCurrencyPaid->save();

        return $logCurrencyPaid->id;
    }

    /**
     * IDから有償一次通貨のログを取得する
     *
     * @param string $id
     * @return LogCurrencyPaid|null
     */
    public function findById(string $id): ?LogCurrencyPaid
    {
        return LogCurrencyPaid::query()->find($id);
    }

    /**
     * 複数のIDから有償一次通貨のログを取得する
     *
     * @param array<string> $ids
     * @return array<LogCurrencyPaid>
     */
    public function findByIds(array $ids): array
    {
        return LogCurrencyPaid::query()
            ->whereIn('id', $ids)
            ->get()
            ->all();
    }

    /**
     * 複数のIDから有償一次通貨のログを取得する
     * 一次通貨返却用で、seq_no降順で取得する
     *
     * @param array<string> $ids
     * @return array<LogCurrencyPaid>
     */
    public function findByIdsOrderBySeqNo(array $ids): array
    {
        return LogCurrencyPaid::query()
            ->whereIn('id', $ids)
            ->orderBy('seq_no', 'desc')
            ->get()
            ->all();
    }

    /**
     * ユーザーIDの関連するログを全て返す
     *
     * @param string $userId
     * @return array<LogCurrencyPaid>
     */
    public function findByUserId(string $userId): array
    {
        return LogCurrencyPaid::query()
            ->where('usr_user_id', $userId)
            ->get()
            ->all();
    }

    /**
     * 最初に作成されたレコードを取得
     *
     * @return LogCurrencyPaid|null
     */
    public function getFirstRecord(): ?LogCurrencyPaid
    {
        return LogCurrencyPaid::orderBy('created_at')
            ->first();
    }

    /**
     * 日本通貨の集計用ログデータ取得
     *
     * @param Carbon $createdAt
     * @param bool $isIncludeSandbox sandboxのデータを含めるか(false:含めない、true:含める)
     * @param string|null $billingPlatform 集計する課金プラットフォーム
     * @return Collection<int, LogCurrencyPaid>
     */
    public function getCurrencyAggregationByJPY(
        Carbon $createdAt,
        bool $isIncludeSandbox,
        ?string $billingPlatform
    ): Collection {
        $query = $this->getAggregateDataBuilder($createdAt, $isIncludeSandbox);

        if (!is_null($billingPlatform)) {
            // プラットフォームに指定があれば条件に追加する
            // nullだった場合は全プラットフォームを対象にする
            $query->where('billing_platform', $billingPlatform);
        }

        $query
            ->where('currency_code', 'JPY')
            ->groupBy('trigger_type', 'price_per_amount');

        return $query->get();
    }

    /**
     * 日本以外の通貨の集計用ログデータ取得
     *
     * @param Carbon $createdAt
     * @param bool $isIncludeSandbox sandboxのデータを含めるか(false:含めない、true:含める)
     * @return Collection<int, LogCurrencyPaid>
     */
    public function getCurrencyAggregationByNotJPY(
        Carbon $createdAt,
        bool $isIncludeSandbox
    ): Collection {
        $query = $this->getAggregateDataBuilder($createdAt, $isIncludeSandbox);
        $query
            ->addSelect('currency_code')
            ->whereIn('currency_code', ['JPY'], not: true)
            ->groupBy('currency_code', 'trigger_type', 'price_per_amount');

        return $query->get();
    }

    /**
     * 集計用ログデータ取得用の共通クエリビルダー
     *
     * @param Carbon $createdAt メソッド内でUTCとして扱う
     * @param bool $isIncludeSandbox sandboxのデータを含めるか(false:含めない、true:含める)
     * @return Builder<LogCurrencyPaid>
     */
    private function getAggregateDataBuilder(
        Carbon $createdAt,
        bool $isIncludeSandbox,
    ): Builder {
        // createdAtをUTCとして扱う
        // 元のオブジェクトのTZが変わらないようにcloneする
        $createdAtUtc = $createdAt->clone()->utc();

        $query = LogCurrencyPaid::query()
            ->select('trigger_type', 'price_per_amount', DB::raw('SUM(change_amount) as sum_amount'))
            // 単価0のログは集計から除く
            ->where('price_per_amount', '<>', 0)
            ->where('created_at', '<=', $createdAtUtc);

        if (!$isIncludeSandbox) {
            // sandboxデータを含めない場合は、is_sandbox=falseのデータのみ取得する
            $query->where('is_sandbox', 0);
        }

        return $query;
    }

    /**
     * コラボ消費通貨の集計結果配列を返す
     *
     * @param Carbon $startAt メソッド内でUTCとして扱う
     * @param Carbon $endAt メソッド内でUTCとして扱う
     * @param array<array{type: string, ids: array<string>}> $searchTriggers
     * @param bool $isIncludeSandbox sandboxのデータを含めるか(false:含めない、true:含める)
     * @param array<int, array<string, string>> $revertLogCurrencyPaidIdList
     * 一次通貨返却で返却されたlog_currency_paidsのrevert_log_currency_paid_idとlog_currency_paid_idの配列
     * @return array<mixed> LogCurrencyPaidとは違うselect結果を返すため、配列にしている
     */
    public function getCollaboAggregation(
        Carbon $startAt,
        Carbon $endAt,
        array $searchTriggers,
        bool $isIncludeSandbox,
        array $revertLogCurrencyPaidIdList,
    ): array {
        // startAtとendAtをUTCとして扱う
        //   元のオブジェクトのTZが変わらないようにcloneする
        $startAtUtc = $startAt->clone()->utc();
        $endAtUtc = $endAt->clone()->utc();

        // trigger_type, trigger_id, 消費年月(year_month_created_at), currency_code, price_per_amount, 消費有償通貨数
        // 返却でsum_amountが変動するため、返却確認用にgroup化したidをlog_currency_paid_idsにまとめている(return時に削除)
        $query = LogCurrencyPaid::query()
            ->select(
                'trigger_type',
                'trigger_id',
                DB::raw(
                    'DATE_FORMAT('
                    . 'CONVERT_TZ(created_at,'
                    . "'" . CurrencyConstants::DATABASE_TZ . "',"
                    . "'" . CurrencyConstants::OUTPUT_TZ . "'"
                    . '),' // UCTからJSTに変換
                    . "'%Y-%m'"
                    . ') AS year_month_created_at'
                ),
                'currency_code',
                'price_per_amount',
                // 表示用に絶対値を取る
                DB::raw('-1 * SUM(change_amount) as sum_amount'),
                DB::raw('GROUP_CONCAT(id) as log_currency_paid_ids'),
            )
            // 消費のみを対象にする
            ->where('change_amount', '<', 0)
            // 消費はquery updateになっている
            ->where('query', LogCurrencyPaid::QUERY_UPDATE)
            // 期間を指定する
            ->whereBetween('created_at', [$startAtUtc, $endAtUtc]);
        // 検索条件に合致するもののみを対象にする
        $query->where(function ($query) use ($searchTriggers) {
            foreach ($searchTriggers as $searchTrigger) {
                $query->orWhere(function ($query) use ($searchTrigger) {
                    $query
                        ->where('trigger_type', $searchTrigger['type'])
                        ->whereIn('trigger_id', $searchTrigger['ids']);
                });
            }
        });
        if (!$isIncludeSandbox) {
            // sandboxデータを含めない場合は、is_sandbox=falseのデータのみ取得する
            $query->where('is_sandbox', 0);
        }
        // グループ化
        $query
            ->groupBy('trigger_type', 'trigger_id', 'year_month_created_at', 'currency_code', 'price_per_amount');
        $collaboAggregationResult = $query->get()->toArray();

        // 返却対象のidがあるとき、sum_amountを変動させる必要がある
        if ($revertLogCurrencyPaidIdList !== []) {
            // 返却対象の消費ログIDリストを取得
            $revertLogIdList = array_column($revertLogCurrencyPaidIdList, 'revert_log_currency_paid_id');
            // 返却ログIDリストを取得し、返却ログをまとめておく
            $logIdList = array_column($revertLogCurrencyPaidIdList, 'log_currency_paid_id');
            $revertLogData = LogCurrencyPaid::query()
                ->select('id', 'change_amount')
                ->whereIn('id', $logIdList)
                ->get();

            // 返却データがある場合はsum_amountからchange_amount分を減算する
            $result = [];
            foreach ($collaboAggregationResult as $row) {
                $rowSumAmount = (int)$row['sum_amount'];
                $totalRevertAmount = 0;
                $aggregationLogIds = explode(',', $row['log_currency_paid_ids']);
                // 集計対象のidリストから返却対象があるかを調べる
                foreach ($aggregationLogIds as $logCurrencyPaidId) {
                    if (!in_array($logCurrencyPaidId, $revertLogIdList)) {
                        // 返却対象のidでない場合はスキップ
                        continue;
                    }
                    // 返却対象のidだった場合、返却logを取得する
                    $logId = collect($revertLogCurrencyPaidIdList)
                        ->where('revert_log_currency_paid_id', $logCurrencyPaidId)
                        ->first();
                    $revertData = $revertLogData->where('id', $logId['log_currency_paid_id'])->first();
                    $totalRevertAmount += $revertData['change_amount'];
                }
                // 返却個数がある場合は計算
                if ($totalRevertAmount > 0) {
                    $newSumAmount = $rowSumAmount - $totalRevertAmount;
                    if ($newSumAmount > 0) {
                        // 全て返却されていて合計が0より大きい場合のみリザルトに新しいsum_amountを設定して返す
                        $newRow = $row;
                        $newRow['sum_amount'] = (string)$newSumAmount;
                        $result[] = $newRow;
                    }
                } else {
                    // 特に返却はなかったのでそのままリザルトに追加
                    $result[] = $row;
                }
            }
            $collaboAggregationResult = $result;
        }
        // $collaboAggregationResultからlog_currency_paid_idsカラムを削除してreturn
        return array_map(function ($item) {
            unset($item['log_currency_paid_ids']);
            return $item;
        }, $collaboAggregationResult);
    }
}
