<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Constants\CurrencyCode;
use App\Constants\ProductType;
use App\Entities\Product\ProductList;
use App\Models\Adm\AdmBankF002;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Log\LogStore;
use App\Models\Opr\OprProduct;
use App\Models\Usr\UsrCurrencyPaid;
use App\Repositories\Adm\AdmBankF002Repository;
use App\Services\EnvironmentService;
use App\Services\ProductService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Delegators\CurrencyAdminDelegator;
use WonderPlanet\Domain\Currency\Entities\ForeignCurrencyMonthlyRateEntity;

/**
 * Bank KPI f002ログサービス
 */
class BankF002Service
{
    public function __construct(
        // Repository
        private AdmBankF002Repository $admBankF002Repository,
        // Service
        private BankService $bankService,
        private EnvironmentService $environmentService,
        private ProductService $productService,
        // Delegator
        private CurrencyAdminDelegator $currencyAdminDelegator,
    ) {
    }

    /**
     * logCurrencyPaidを元にBankF002を生成する
     *
     * @param string $env
     * @param Collection<LogCurrencyPaid> $logCurrencyPaids
     * @param CarbonImmutable $now
     * @param Collection<string, ForeignCurrencyMonthlyRateEntity> $foreignCurrencyMonthlyRateEntities
     * @return Collection<AdmBankF002>
     */
    public function createLogByCurrencyPaids(
        string          $env,
        Collection      $logCurrencyPaids,
        CarbonImmutable $now,
        Collection      $foreignCurrencyMonthlyRateEntities,
    ): Collection {
        $applicationId = $this->environmentService->getApplicationId();
        $fluentdTag = $this->bankService->getFluentdTag($env, $applicationId, BankKPIFormatType::F002->value);

        // LogCurrencyPaidに該当するUsrCurrencyPaidを取得し、そのレコードが生成された日付に基づく為替で計算する
        $usrCurrencyPaids = UsrCurrencyPaid::query()
            ->select('id', 'created_at',)
            ->whereIn('id', $logCurrencyPaids->pluck('currency_paid_id'))
            ->get()
            ->keyBy('id');

        // billing_platformでグループ化して商品情報を取得
        $productLists = $this->getProductListsByBillingPlatform($logCurrencyPaids);

        $models = $logCurrencyPaids->map(function (LogCurrencyPaid $log) use (
            $fluentdTag,
            $applicationId,
            $usrCurrencyPaids,
            $foreignCurrencyMonthlyRateEntities,
            $productLists,
        ) {
            $rate = $this->getForeignCurrencyRate(
                $foreignCurrencyMonthlyRateEntities,
                $log,
                $usrCurrencyPaids->get($log->getCurrencyPaidId())
            );
            $buyCoin = 0;
            $buyAmount = 0;
            $payCoin = 0;
            $payAmount = 0;
            if ($log->getChangeAmount() > 0) {
                $buyCoin = $log->getChangeAmount();

                // パック商品の場合は有償通貨以外の部分を計算
                $directPrice = 0;
                $paidDiamondPrice = $this->getPaidDiamondPriceForPackProduct($productLists, $log);
                if ($paidDiamondPrice > 0) {
                    // 有償通貨以外の部分 = 購入価格 - 有償通貨価格
                    $directPrice = $log->getPurchasePrice() - $paidDiamondPrice;
                }

                // 有償通貨部分のみを計上
                $buyAmount = (float)($log->getPurchasePrice() - $directPrice) * $rate;
            } else {
                $payCoin = -$log->getChangeAmount();
                $payAmount = (float)($log->getPricePerAmount() * -$log->getChangeAmount()) * $rate;
            }

            return $this->admBankF002Repository->createModel(
                $fluentdTag,
                BankKPIConstant::VERSION,
                $applicationId,
                $log->getUsrUserId(),
                '', // 未使用
                $this->bankService->getPlatformIdByOsPlatformAndBillingPlatform(
                    $log->getOsPlatform(),
                    $log->getBillingPlatform()
                ),
                $buyCoin,
                $buyAmount,
                $payCoin,
                $payAmount,
                0, // 直接課金集計はLogStoreで対応
                0, // サブスク課金集計はLogStoreで対応
                $log->getTriggerId(),
                $log->getCreatedAt(),
                CurrencyCode::JPY->value, // JPで固定
                CurrencyCode::JPY->name,  // JPYで固定
            );
        });
        $this->admBankF002Repository->bulkInsert($models, $now);
        return $models;
    }

    /**
     * @param Collection<string, ForeignCurrencyMonthlyRateEntity> $foreignCurrencyMonthlyRateEntities
     * @param LogCurrencyPaid $logCurrencyPaid
     * @param UsrCurrencyPaid|null $usrCurrencyPaid
     * @return float
     */
    private function getForeignCurrencyRate(
        Collection      $foreignCurrencyMonthlyRateEntities,
        LogCurrencyPaid $logCurrencyPaid,
        ?UsrCurrencyPaid $usrCurrencyPaid,
    ): float {
        if ($logCurrencyPaid->getCurrencyCode() === CurrencyCode::JPY->value) {
            return 1.0; // JPYはそのまま
        }

        if ($usrCurrencyPaid === null) {
            return 1.0; // UsrCurrencyPaidがない場合は1.0を返す
        }

        // UsrCurrencyPaidのcreated_atの前日から為替レートを取得
        $targetForeignCurrencyRateAt = $usrCurrencyPaid->created_at->subDay();
        $targetForeignCurrencyRateKey = $targetForeignCurrencyRateAt->format('Ym');
        if (!$foreignCurrencyMonthlyRateEntities->has($targetForeignCurrencyRateKey)) {
            $foreignCurrencyMonthlyRateEntities
                ->put(
                    $targetForeignCurrencyRateKey,
                    $this->currencyAdminDelegator->getForeignCurrencyMonthlyRate(
                        $targetForeignCurrencyRateAt->year,
                        $targetForeignCurrencyRateAt->month
                    )
                );
        }

        return $foreignCurrencyMonthlyRateEntities
            ->get($targetForeignCurrencyRateKey)
            ->getRate($targetForeignCurrencyRateAt->day, $logCurrencyPaid->getCurrencyCode());
    }

    /**
     * logCurrencyPaidを元にBankF002を生成する
     *
     * @param string $env
     * @param Collection<LogStore> $logStores
     * @param Collection<OprProduct> $oprProducts
     * @param CarbonImmutable $now
     * @param ForeignCurrencyMonthlyRateEntity $foreignCurrencyMonthlyRateEntity
     * @return Collection<AdmBankF002>
     */
    public function createLogByStores(
        string                           $env,
        Collection                       $logStores,
        Collection                       $oprProducts,
        CarbonImmutable                  $now,
        ForeignCurrencyMonthlyRateEntity $foreignCurrencyMonthlyRateEntity,
    ): Collection {
        $applicationId = $this->environmentService->getApplicationId();
        $fluentdTag = $this->bankService->getFluentdTag($env, $applicationId, BankKPIFormatType::F002->value);

        // logStoresからoprProductsに無いOprProductのIDを取得
        $oprProductIds = $logStores->map(function (LogStore $log) use ($oprProducts) {
            return $oprProducts->contains('id', $log->getProductSubId()) ? null : $log->getProductSubId();
        })->filter()->unique();

        // 足りないOprProductを取得して足しこむ
        OprProduct::query()
            ->whereIn('id', $oprProductIds)
            ->get()
            ->each(function (OprProduct $oprProduct) use ($oprProducts) {
                $oprProducts->put(
                    $oprProduct->id,
                    $oprProduct,
                );
            });

        // billing_platformでグループ化して商品情報を取得
        $productLists = $this->getProductListsByBillingPlatform($logStores);

        $models = $logStores->map(function (LogStore $log) use (
            $fluentdTag,
            $applicationId,
            $oprProducts,
            $foreignCurrencyMonthlyRateEntity,
            $productLists,
        ) {
            $directAmount = 0;
            $subscriptionAmount = 0;
            if ($log->getCurrencyCode() !== CurrencyCode::JPY->value) {
                $day = $log->created_at->copy()->timezone(CurrencyConstants::OUTPUT_TZ)->day;
                $rate = $foreignCurrencyMonthlyRateEntity->getRate($day, $log->getCurrencyCode());
            } else {
                $rate = 1.0; // JPYはそのまま
            }

            /** @var OprProduct $oprProduct */
            $oprProduct = $oprProducts->get($log->getProductSubId());
            $productType = $oprProduct?->product_type;
            if ($productType === ProductType::PACK->value) {
                // パック商品: ベース金額から有償通貨分を差し引く
                $paidDiamondPrice = $this->getPaidDiamondPriceForPackProduct($productLists, $log);
                $directAmount = (float)($log->getPurchasePrice() - $paidDiamondPrice) * $rate;
            } elseif ($productType === ProductType::PASS->value) {
                // パス商品: ベース金額をそのまま使用
                $directAmount = (float)($log->getPurchasePrice()) * $rate;
            } else {
                // 不明な商品タイプの場合はスキップ
                return null;
            }

            return $this->admBankF002Repository->createModel(
                $fluentdTag,
                BankKPIConstant::VERSION,
                $applicationId,
                $log->getUsrUserId(),
                '', // 未使用
                $this->bankService->getPlatformIdByOsPlatformAndBillingPlatform(
                    $log->getOsPlatform(),
                    $log->getBillingPlatform()
                ),
                0,
                0,
                0,
                0,
                $directAmount,
                $subscriptionAmount,
                $log->getTriggerId(),
                $log->getCreatedAt(),
                CurrencyCode::JPY->value, // JPで固定
                CurrencyCode::JPY->name,  // JPYで固定
            );
        })->filter()->values();
        $this->admBankF002Repository->bulkInsert($models, $now);
        return $models;
    }

    /**
     * BankF002のデータを整形する
     *
     * @param Collection<AdmBankF002> $admBankF002s
     * @return Collection<string>
     */
    public function formatDataRecords(
        Collection $admBankF002s
    ): Collection {
        return $admBankF002s->map(function (AdmBankF002 $admBankF002) {
            return implode("\t", [
                CarbonImmutable::parse($admBankF002->getInsertTime())->format('Y-m-d\TH:i:s\Z') ?? '',
                $admBankF002->getFluentdTag(),
                json_encode([
                    'version' => $admBankF002->getVersion(),
                    'app_id' => $admBankF002->getAppId(),
                    'client_id' => $this->environmentService->getClientId(),
                    'client_secret' => $this->environmentService->getClientSecret(),
                    'app_user_id' => $admBankF002->getAppUserId(),
                    'app_system_prefix' => $admBankF002->getAppSystemPrefix(),
                    'buy_coin' => $admBankF002->getBuyCoin(),
                    'buy_amount' => $admBankF002->getBuyAmount(),
                    'pay_coin' => $admBankF002->getPayCoin(),
                    'pay_amount' => $admBankF002->getPayAmount(),
                    'direct_amount' => $admBankF002->getDirectAmount(),
                    'subscription_amount' => $admBankF002->getSubscriptionAmount(),
                    'item_id' => $admBankF002->getItemId(),
                    'list_price' => 0, // TODO: 不要かもしれないので要確認
                    'platform_id' => $admBankF002->getPlatformId(),
                    'insert_time' => $admBankF002->getInsertTime(),
                    'country_code' => $admBankF002->getCountryCode(),
                    'currency_code' => $admBankF002->getCurrencyCode(),
                ]),
            ]);
        });
    }

    /**
     * billing_platformごとに商品情報を取得する
     *
     * @param Collection<LogCurrencyPaid|LogStore> $logs
     * @return Collection<string, ProductList>
     */
    private function getProductListsByBillingPlatform(Collection $logs): Collection
    {
        $productLists = collect();
        $logs->groupBy(function ($log) {
            return $log->getBillingPlatform();
        })->each(function (Collection $logs, $billingPlatform) use ($productLists) {
            $productList = $this->productService->getProductListByReceiptUniqueIds(
                $billingPlatform,
                $logs->map(function ($log) {
                    return $log->getReceiptUniqueId();
                })
            );
            $productLists->put($billingPlatform, $productList);
        });
        return $productLists;
    }

    /**
     * パック商品の有償通貨価格を取得する（パック商品でない場合は0を返す）
     *
     * @param Collection<string, ProductList> $productLists
     * @param LogCurrencyPaid|LogStore $log
     * @return float
     */
    private function getPaidDiamondPriceForPackProduct(
        Collection $productLists,
        LogCurrencyPaid|LogStore $log
    ): float {
        // ProductListから商品情報を取得
        $productList = $productLists->get($log->getBillingPlatform());
        if (!$productList) {
            return 0.0;
        }
        $oprProductId = $productList->getReceiptToOprProductId($log->getReceiptUniqueId());
        $productInfo = $productList->getProductInfo($oprProductId);

        if (!$productInfo || $productInfo->getProductType() !== ProductType::PACK->value) {
            return 0.0;
        }

        // platformIdを決定（WebStoreの場合は'asb'、それ以外はos_platform）
        $platformId = ($log->getBillingPlatform() === CurrencyConstants::PLATFORM_WEBSTORE)
            ? BankKPIConstant::PLATFORM_ASB
            : $log->getOsPlatform();

        return $productInfo->getPaidDiamondPrice($platformId);
    }
}
