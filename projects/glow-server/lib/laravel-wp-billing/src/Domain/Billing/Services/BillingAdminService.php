<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Billing\Repositories\LogStoreRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreProductHistoryRepository;
use WonderPlanet\Domain\Billing\Traits\BillingCollectTrait;
use WonderPlanet\Domain\Billing\Traits\BillingPurchaseTrait;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;
use WonderPlanet\Domain\Billing\Utils\Excel\BillingLogReport;
use WonderPlanet\Domain\Billing\Utils\StoreUtility;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalAdminDelegator;
use WonderPlanet\Domain\Currency\Delegators\CurrencyInternalDelegator;
use WonderPlanet\Domain\Currency\Entities\Trigger;
use WonderPlanet\Domain\Currency\Facades\CurrencyCommon;
use WonderPlanet\Domain\Currency\Models\AdmForeignCurrencyRate;
use WonderPlanet\Domain\Currency\Repositories\AdmForeignCurrencyRateRepository;
use WonderPlanet\Domain\Currency\Repositories\MstStoreProductRepository;
use WonderPlanet\Domain\Currency\Repositories\OprProductRepository;

class BillingAdminService
{
    use BillingPurchaseTrait;
    use BillingCollectTrait;
    use FakeStoreReceiptTrait;

    private const RECEIPT_PAYLOAD_GRANT = 'GrantByTool';
    private const RECEIPT_PAYLOAD_COLLECT = 'CollectByTool';

    public function __construct(
        private LogStoreRepository $logStoreRepository,
        private AdmForeignCurrencyRateRepository $admForeignCurrencyRateRepository,
        private MstStoreProductRepository $mstStoreProductRepository,
        private OprProductRepository $oprProductRepository,
        private CurrencyInternalDelegator $currencyInternalDelegator,
        private UsrStoreProductHistoryRepository $usrStoreProductHistoryRepository,
        private UsrStoreInfoRepository $usrStoreInfoRepository,
        private CurrencyInternalAdminDelegator $currencyInternalAdminDelegator,
    ) {
    }

    /**
     * LogStoresの初回レコードと現在年月から選択オプションを生成して取得
     *
     * @return  mixed[]
     */
    public function getYearMonthOptions(): array
    {
        /** @var \WonderPlanet\Domain\Billing\Models\LogStore|null $logStore */
        $logStore = $this->logStoreRepository->getFirstRecord();

        if (is_null($logStore)) {
            // レコードが存在しない場合は空配列を返す
            return [[], []];
        }

        // 「選択可能な開始年月」を取得
        $logStoreCreatedAt =  $logStore->created_at;

        // 開始年月と現在年月が一緒の場合は空配列を返す(取得可能なデータが無いため)
        $now = Carbon::now();
        if ($logStoreCreatedAt->format('Y-m') === $now->format('Y-m')) {
            return [[], []];
        }

        // 「選択可能な終了年月」を取得(現在月の前月)
        $endAt = $now->clone()->subMonthNoOverflow();

        // 年の範囲をループして配列を生成
        $years = [];
        $monthsByYear = [];
        for ($year = $logStoreCreatedAt->year; $year <= $endAt->year; $year++) {
            // 年配列の生成
            $yearStr = (string) $year;
            $years[$yearStr] = $yearStr;

            // 月配列の生成
            // ループがログデータの年ならログデータの月から、そうでなければ1月から開始する
            $startMonth = $year === $logStoreCreatedAt->year
                ? (string) $logStoreCreatedAt->month
                : '1';

            // ループが現在年の場合は、現在月-1までの月数、現在年以外なら12固定
            $endMonth = $year === $endAt->year ? $endAt->month : '12';
            $monthsByYear[$yearStr] = array_combine(range($startMonth, $endMonth), range($startMonth, $endMonth));
        }

        return [$years, $monthsByYear];
    }

    /**
     * 課金ログレポートオブジェクトを取得
     *
     * @param string $year
     * @param string $month
     * @param bool $isIncludeSandbox
     * @param int $limit
     * @return BillingLogReport
     */
    public function getBillingLogReport(
        string $year,
        string $month,
        bool $isIncludeSandbox,
        int $limit
    ): BillingLogReport {
        // 日本時間として生成
        $specifiedJstAt = Carbon::createFromFormat('Y-m', "{$year}-{$month}", 'Asia/Tokyo');

        // 月の初日から3日前の日時を取得
        $startJstAt = $specifiedJstAt->copy()->startOfMonth()->subDays(3);

        // 月の最終日の3日後の日時を取得
        $endJstAt = $specifiedJstAt->copy()->endOfMonth()->addDays(3);

        // 外貨為替レートデータ取得
        $currencyCodeAndTtmList = $this->admForeignCurrencyRateRepository
            ->getCollectionByYearAndMonth((int) $year, (int) $month)
            ->map(function (AdmForeignCurrencyRate $row) {
                return [
                    'currency_code' => $row->currency_code,
                    'ttm' => $row->ttm,
                ];
            })->toArray();

        // 抽出対象の総件数を取得
        $allCount = $this->logStoreRepository
            ->getTargetMonthCount(
                $startJstAt,
                $endJstAt,
                $isIncludeSandbox,
            );

        // クエリ分割実行回数を取得
        //  メモリ消費を抑えるために分割実行で取得している
        //  サーバー環境のスペックによっては、1回のクエリ実行に時間がかかる可能性がある
        //  あまりに遅い場合は一括実行またはindexのチューニングなどを検討すること
        $spins = ceil($allCount / $limit);
        $allData = new Collection();
        for ($offset = 0; $offset <= $spins; $offset++) {
            $chunkData = $this->logStoreRepository->getTargetMonthData(
                $startJstAt,
                $endJstAt,
                $isIncludeSandbox,
                $currencyCodeAndTtmList,
                $offset,
                $limit
            );
            $allData->push($chunkData->toArray());
        }

        // 階層を統一したコレクションにする
        $allData = $allData->flatten(1);

        // オブジェクトを生成して返す
        return new BillingLogReport(
            $year,
            $month,
            $isIncludeSandbox,
            $allData
        );
    }

    /**
     * 購入処理(管理画面用)
     * 管理画面用に処理を抜き出しているので、BillingServiceの処理とは下記が異なる
     *  購入許可情報(usr_store_allowance)のチェック/削除処理はしない
     *  レシート検証は行わず、ダミーデータを生成/登録している
     *
     * @param string $userId
     * @param string $osPlatform
     * @param string $billingPlatform
     * @param string $deviceId
     * @param string $storeProductId
     * @param string $mstStoreProductId
     * @param string $productSubId
     * @param string $purchasePrice
     * @param string $rawPriceString
     * @param int $vipPoint
     * @param string $currencyCode
     * @param string $receiptUniqueId
     * @param Trigger $trigger
     * @param string $loggingProductSubName
     * @param callable $callback
     * @param bool $isSandbox
     * @return void
     * @throws \WonderPlanet\Domain\Billing\Exceptions\WpBillingException
     * @throws \WonderPlanet\Domain\Currency\Exceptions\WpCurrencyAddCurrencyOverByMaxException
     */
    public function purchasedByTool(
        string $userId,
        string $osPlatform,
        string $billingPlatform,
        string $deviceId,
        string $storeProductId,
        string $mstStoreProductId,
        string $productSubId,
        string $purchasePrice,
        string $rawPriceString,
        int $vipPoint,
        string $currencyCode,
        string $receiptUniqueId,
        Trigger $trigger,
        string $loggingProductSubName,
        callable $callback,
        bool $isSandbox
    ): void {
        // configからbundleIdを取得(AppStore:bundleId、GoogleStore:packageName)
        $bundleId = StoreUtility::getBundleIdOrPackageName($isSandbox, $billingPlatform);

        $purchaseToken = 'dummy_purchase_token';

        // ダミーのレシート情報を取得
        $receiptStr = $this->makeReceiptAdmin(self::RECEIPT_PAYLOAD_GRANT);

        // 購入処理実行
        $this->executePurchase(
            $userId,
            $osPlatform,
            $billingPlatform,
            $deviceId,
            $storeProductId,
            $mstStoreProductId,
            $productSubId,
            $purchasePrice,
            $rawPriceString,
            $vipPoint,
            $currencyCode,
            $receiptUniqueId,
            $bundleId,
            $purchaseToken,
            $receiptStr,
            $trigger,
            $loggingProductSubName,
            $callback,
            $isSandbox
        );
    }

    /**
     * 管理ツールで登録する用のレシート情報を作成する
     *
     * @return string
     */
    private function makeReceiptAdmin(string $payload): string
    {
        // トランザクションIDは重複しなければ良いので、操作と紐付けやすくリクエストIDを使用
        $requestUniqueIdData = CurrencyCommon::getRequestUniqueIdData();
        $uniqueId = $requestUniqueIdData->getRequestIdType()->value . ':' . $requestUniqueIdData->getRequestId();

        return <<< EOM
        {
            "Payload":"{$payload}",
            "Store":"admin",
            "TransactionID":"{$uniqueId}"
        }
        EOM;
    }

    /**
     * 購入情報を返品した状態にする
     *
     * @param string $userId
     * @param string  $usrStoreProductHistoryId
     * @param string  $deviceId
     * @param string  $receiptBundleId
     * @param string  $receiptPurchaseToken
     * @param string  $receiptUniqueId
     * @param Trigger $trigger
     * @return void
     * @throws \Exception
     */
    public function returnedPurchase(
        string $userId,
        string $usrStoreProductHistoryId,
        string $deviceId,
        string $receiptBundleId,
        string $receiptPurchaseToken,
        string $receiptUniqueId,
        Trigger $trigger
    ): void {
        $receiptStr = $this->makeReceiptAdmin(self::RECEIPT_PAYLOAD_COLLECT);

        // 回収処理実行
        $this->executeCollect(
            $userId,
            $usrStoreProductHistoryId,
            $deviceId,
            $receiptBundleId,
            $receiptPurchaseToken,
            $receiptUniqueId,
            $trigger,
            $receiptStr
        );
    }
}
