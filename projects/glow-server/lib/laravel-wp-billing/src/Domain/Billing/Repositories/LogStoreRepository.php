<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use WonderPlanet\Domain\Billing\Models\LogStore;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;

/**
 * ショップ購入ログを管理するRepository
 */
class LogStoreRepository
{
    /**
     * ショップ購入ログを登録する
     *
     * productSubNameについて
     *   購入した時のproduct_sub_idに対応した名称を入れる
     *   (そのユーザーに対応した言語の名称を入れるか、日本語など特定の言語名称を入れるかはプロダクト側の判断)
     *   product_sub_idだけでは、マスタから情報が消えた時にどのアイテムが購入されたかわからなくなるため記録している
     *
     * seqNoについて
     *   パス商品など、有償一次通貨を配布しない商品を登録する時、seq_noがとれないのでnullとする。
     *   seq_noは消費順を表しているため、0で代用すると混乱する可能性があるのでnullとしている
     *
     * @param string $userId
     * @param string $deviceId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param integer $age
     * @param integer|null $seqNo
     * @param string $platformProductId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $productSubName
     * @param string $rawReceipt
     * @param string $rawPriceString
     * @param string $currencyCode
     * @param string $receiptUniqueId
     * @param string $receiptBundleId
     * @param integer $paidAmount
     * @param integer $freeAmount
     * @param string $purchasePrice
     * @param string $pricePerAmount
     * @param integer $vipPoint
     * @param boolean $isSandbox
     * @param Trigger $trigger
     * @return string
     */
    public function insertStoreLog(
        string $userId,
        string $deviceId,
        string $osPlatform,
        string $billingPlatform,
        int $age,
        ?int $seqNo,
        string $platformProductId,
        string $mstStoreProductId,
        string $productSubId,
        string $productSubName,
        string $rawReceipt,
        string $rawPriceString,
        string $currencyCode,
        string $receiptUniqueId,
        string $receiptBundleId,
        int $paidAmount,
        int $freeAmount,
        string $purchasePrice,
        string $pricePerAmount,
        int $vipPoint,
        bool $isSandbox,
        Trigger $trigger
    ): string {
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();

        $logStore = new LogStore();

        $logStore->usr_user_id = $userId;
        $logStore->os_platform = $osPlatform;
        $logStore->billing_platform = $billingPlatform;
        $logStore->device_id = $deviceId;
        $logStore->age = $age;
        $logStore->seq_no = $seqNo;
        $logStore->platform_product_id = $platformProductId;
        $logStore->mst_store_product_id = $mstStoreProductId;
        $logStore->product_sub_id = $productSubId;
        $logStore->product_sub_name = $productSubName;
        $logStore->raw_receipt = $rawReceipt;
        $logStore->raw_price_string = $rawPriceString;
        $logStore->currency_code = $currencyCode;
        $logStore->receipt_unique_id = $receiptUniqueId;
        $logStore->receipt_bundle_id = $receiptBundleId;
        $logStore->paid_amount = $paidAmount;
        $logStore->free_amount = $freeAmount;
        $logStore->purchase_price = $purchasePrice;
        $logStore->price_per_amount = $pricePerAmount;
        $logStore->vip_point = $vipPoint;
        $logStore->is_sandbox = (int)$isSandbox;
        $logStore->trigger_type = $trigger->triggerType;
        $logStore->trigger_id = $trigger->triggerId;
        $logStore->trigger_name = $trigger->triggerName;
        $logStore->trigger_detail = $trigger->triggerDetail;
        $logStore->request_id_type = $requestUniqueIdData->getRequestIdType()->value;
        $logStore->request_id = $requestUniqueIdData->getRequestId();
        $logStore->nginx_request_id = CurrencyCommon::getFrontRequestId();

        $logStore->save();

        return $logStore->id;
    }

    /**
     * 指定したIDのログを取得する
     *
     * @param string $id
     * @return LogStore|null
     */
    public function findById(string $id): ?LogStore
    {
        return LogStore::query()->find($id);
    }

    /**
     * 指定したユーザーIDのログを取得する
     *
     * @param string $userId
     * @return array<LogStore>
     */
    public function findByUserId(string $userId): array
    {
        return LogStore::query()
            ->where('usr_user_id', $userId)
            ->get()
            ->all();
    }

    /**
     * 最初に作成されたレコードを取得
     *
     * @return LogStore|null
     */
    public function getFirstRecord(): ?LogStore
    {
        return LogStore::orderBy('created_at')
            ->first();
    }

    /**
     * 指定した日時とisSandboxから対象のログデータ件数を取得
     *
     * @param Carbon $startAt メソッド内でUTCとして扱う
     * @param Carbon $endAt メソッド内でUTCとして扱う
     * @param bool $isIncludeSandbox sandboxのデータを含めるか(false:含めない、true:含める)
     * @return int
     */
    public function getTargetMonthCount(Carbon $startAt, Carbon $endAt, bool $isIncludeSandbox): int
    {
        // startAtとendAtをUTCとして扱う
        //   元のオブジェクトのTZが変わらないようにcloneする
        $startAtUtc = $startAt->clone()->utc();
        $endAtUtc = $endAt->clone()->utc();

        $query = LogStore::query()
            ->whereBetween('created_at', [$startAtUtc, $endAtUtc]);

        if (!$isIncludeSandbox) {
            // sandboxデータを含めない場合は、is_sandbox=falseのデータのみ取得する
            $query->where('is_sandbox', 0);
        }

        return $query->count();
    }

    /**
     * 指定した日時とisSandboxからログデータを取得
     *
     * @param Carbon $startAt メソッド内でUTCとして扱う
     * @param Carbon $endAt メソッド内でUTCとして扱う
     * @param bool $isIncludeSandbox sandboxのデータを含めるか(false:含めない、true:含める)
     * @param array<int, array<string, string>> $currencyCodeAndTtmList
     * @param int $offset
     * @param int $limit
     * @return \Illuminate\Support\Collection<int, LogStore>
     */
    public function getTargetMonthData(
        Carbon $startAt,
        Carbon $endAt,
        bool $isIncludeSandbox,
        array $currencyCodeAndTtmList,
        int $offset = 0,
        int $limit = 1000
    ): \Illuminate\Support\Collection {
        // startAtとendAtをUTCとして扱う
        //   元のオブジェクトのTZが変わらないようにcloneする
        $startAtUtc = $startAt->clone()->utc();
        $endAtUtc = $endAt->clone()->utc();

        $caseQueryStr = $this->makeCurrencyRateCaseQueryStr($currencyCodeAndTtmList);

        $query = LogStore::query()
            ->select([
                'usr_user_id as player_id',
                DB::raw(
                    'CASE'
                    . " WHEN billing_platform = '" . CurrencyConstants::PLATFORM_APPSTORE . "' THEN 'aapl'"
                    . " WHEN billing_platform = '" . CurrencyConstants::PLATFORM_GOOGLEPLAY . "' THEN 'goog'"
                    . " ELSE '不明'"
                    . " END AS market"
                ),
                'receipt_unique_id AS order_id',
                'platform_product_id AS product_id',
                'currency_code AS currency',
                'purchase_price AS price',
                DB::raw($caseQueryStr),
                DB::raw(
                    'DATE_FORMAT('
                    . 'CONVERT_TZ(created_at,'
                    . "'" . CurrencyConstants::DATABASE_TZ . "',"
                    . "'" . CurrencyConstants::OUTPUT_TZ . "'"
                    . '),' // UCTからJSTに変換
                    . "'%Y/%m/%d %H:%i:%s'"
                    . ') AS formatted_created_at'
                ),
            ])
            ->whereBetween('created_at', [$startAtUtc, $endAtUtc])
            ->orderBy('created_at')
            ->orderBy('id');

        $query
            ->limit($limit)
            ->offset($offset * $limit);

        if (!$isIncludeSandbox) {
            // sandboxデータを含めない場合は、is_sandbox=falseのデータのみ取得する
            $query->where('is_sandbox', 0);
        }

        return $query->get();
    }

    /**
     * 外貨為替レート取得用にCASE文を生成する
     *
     * @param array<int, array<string, string>> $currencyCodeAndTtmList
     * @return string
     */
    private function makeCurrencyRateCaseQueryStr(array $currencyCodeAndTtmList): string
    {
        $caseQuery = 'CASE';
        $caseQuery .= " WHEN currency_code = 'JPY' THEN '1'"; // 国内通貨は1固定

        foreach ($currencyCodeAndTtmList as $row) {
            $caseQuery .= " WHEN currency_code = '{$row['currency_code']}' THEN '{$row['ttm']}'";
        }

        $caseQuery .= " ELSE ''";
        $caseQuery .= " END AS currency_rate";

        return $caseQuery;
    }
}
