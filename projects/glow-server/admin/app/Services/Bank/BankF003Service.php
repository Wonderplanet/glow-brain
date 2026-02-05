<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Constants\BankKPISubscriptionUnitType;
use App\Constants\ProductType;
use App\Domain\Resource\Enums\LogCurrencyPaidTriggerType;
use App\Entities\Bank\F003DataEntity;
use App\Entities\Bank\F003DirectDataEntity;
use App\Entities\Bank\F003Entity;
use App\Entities\Bank\F003SubscriptionDataEntity;
use App\Entities\Product\ProductPackInfo;
use App\Entities\Product\ProductPassInfo;
use App\Models\Adm\AdmBankF003;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Log\LogStore;
use App\Repositories\Adm\AdmBankF003Repository;
use App\Repositories\Log\LogCurrencyPaidRepository;
use App\Repositories\Log\LogStoreRepository;
use App\Services\EnvironmentService;
use App\Services\ProductService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Bank KPI f003ログサービス
 */
class BankF003Service
{
    public function __construct(
        // Repository
        private LogCurrencyPaidRepository $logCurrencyPaidRepository,
        private LogStoreRepository $logStoreRepository,
        private AdmBankF003Repository $admBankF003Repository,
        // Service
        private BankService $bankService,
        private EnvironmentService $environmentService,
        private ProductService $productService,
    ) {
    }

    /**
     * BankF003を構成するオブジェクト配列を生成する
     *
     * @param CarbonImmutable $startDate
     * @param CarbonImmutable $endDate
     * @param int $date
     * @return Collection<F003Entity>
     */
    public function createEntities(
        CarbonImmutable $startDate,
        CarbonImmutable $endDate,
        int $date,
    ): Collection {
        $f003Entities = collect();

        // 有償通貨ログを取得
        $step = 1;
        do {
            // 指定ページのログを取得
            $logs = $this->logCurrencyPaidRepository->fetchLogsByDateRange(
                $startDate,
                $endDate,
                ($step - 1) * BankKPIConstant::LOG_FETCH_LIMIT,
                BankKPIConstant::LOG_FETCH_LIMIT
            );

            // ログが空ならループを終了
            if ($logs->isEmpty()) {
                break;
            }

            // 取得したログを処理
            $f003Entities = $this->createEntitiesByCurrencyPaids($f003Entities, $logs, $date);

            // 次のページ
            $step++;
            unset($logs);
        } while (true);

        // ショップ購入ログを取得
        $step = 1;
        do {
            // 指定ページのログを取得
            $logs = $this->logStoreRepository->fetchLogsByDateRange(
                $startDate,
                $endDate,
                ($step - 1) * BankKPIConstant::LOG_FETCH_LIMIT,
                BankKPIConstant::LOG_FETCH_LIMIT
            );

            // ログが空ならループを終了
            if ($logs->isEmpty()) {
                break;
            }

            // 取得したログを処理
            $f003Entities = $this->createEntitiesByStore($f003Entities, $logs, $date);

            // 次のページ
            $step++;
            unset($logs);
        } while (true);

        return $f003Entities;
    }

    /**
     * LogCurrencyPaidを元にBankF003を生成する
     *
     * @param Collection<F003Entity> $f003Entities
     * @param Collection<LogCurrencyPaid> $logCurrencyPaids
     * @param int $date
     * @return Collection<F003Entity>
     */
    private function createEntitiesByCurrencyPaids(
        Collection $f003Entities,
        Collection $logCurrencyPaids,
        int $date,
    ): Collection {
        // billing_platformを考慮した正しいplatform_idでグループ化
        $logCurrencyPaidsGroupByPlatformId = $logCurrencyPaids->groupBy(function (LogCurrencyPaid $item) {
            // WebStoreで購入した通貨の場合は、消費元に関わらず'asb'
            return $this->bankService->getPlatformIdByOsPlatformAndBillingPlatform(
                $item->getOsPlatform(),
                $item->getBillingPlatform()
            );
        });
        $logCurrencyPaidsGroupByPlatformId->each(function (Collection $logs, $platformId) use (
            $f003Entities,
            $date,
        ) {
            $logsGroupByBillingPlatform = $logs->groupBy(function (LogCurrencyPaid $item) {
                return $item->getBillingPlatform();
            });
            $logsGroupByBillingPlatform->each(function (Collection $logs, $billingPlatform) use (
                $f003Entities,
                $date,
                $platformId,
            ) {
                $f003Entity = $f003Entities->get($platformId) ?? new F003Entity($platformId, $date);
                $productList = $this->productService->getProductListByReceiptUniqueIds(
                    $billingPlatform,
                    $logs->map(function (LogCurrencyPaid $log) {
                        return $log->getReceiptUniqueId();
                    })
                );

                $logs->each(function (LogCurrencyPaid $log) use (
                    $f003Entity,
                    $platformId,
                    $productList,
                ) {
                    $triggerType = $log->getTriggerType();

                    // 商品情報が取得できない場合はスキップ
                    $oprProductId = $productList->getReceiptToOprProductId($log->getReceiptUniqueId());
                    $productInfo = $productList->getProductInfo($oprProductId);
                    // 有償通貨を持たない商品はスキップ
                    if (empty($productInfo) || $productInfo->getPaidAmount() <= 0) {
                        return;
                    }

                    // パック商品の場合は有償通貨部分の価格、それ以外は商品全体の価格
                    $productType = $productInfo->getProductType();
                    $price = ($productType === ProductType::PACK->value)
                        ? $productInfo->getPaidDiamondPrice($platformId)
                        : $productInfo->getPrice($platformId);

                    // 価格が0円の商品はスキップ
                    if ($price <= 0) {
                        return;
                    }

                    $data = $f003Entity->getData($oprProductId);
                    if ($data === null) {
                        $data = new F003DataEntity(
                            $productInfo->getName(),
                            $productInfo->getPaidAmount(),
                            $price
                        );
                    }
                    $amount = $log->getChangeAmount();
                    if ($triggerType === LogCurrencyPaidTriggerType::REFUND->value) {
                        $data->addTotalCount($amount);
                    }
                    else {
                        if ($amount > 0) {
                            $data->addTotalCount($amount);
                        } else {
                            $data->addTotalConsumption(-$amount);
                        }
                    }
                    $f003Entity->addData($oprProductId, $data);
                });
                $f003Entities->put($platformId, $f003Entity);
            });
        });
        return $f003Entities;
    }

    /**
     * LogStoreを元にBankF003を生成する
     *
     * @param Collection<F003Entity> $f003Entities
     * @param Collection<LogStore> $logStores
     * @param int $date
     * @return Collection<F003Entity>
     */
    private function createEntitiesByStore(
        Collection $f003Entities,
        Collection $logStores,
        int $date,
    ): Collection {
        // billing_platformを考慮した正しいplatform_idでグループ化
        $logStoresGroupByPlatformId = $logStores->groupBy(function (LogStore $item) {
            // WebStoreで購入した商品の場合は、購入元に関わらず'asb'
            return $this->bankService->getPlatformIdByOsPlatformAndBillingPlatform(
                $item->getOsPlatform(),
                $item->getBillingPlatform()
            );
        });
        $logStoresGroupByPlatformId->each(function (Collection $logs, $platformId) use (
            $f003Entities,
            $date,
        ) {
            $logsGroupByBillingPlatform = $logs->groupBy(function (LogStore $item) {
                return $item->getBillingPlatform();
            });
            $logsGroupByBillingPlatform->each(function (Collection $logs, $billingPlatform) use (
                $f003Entities,
                $date,
                $platformId,
            ) {
                $f003Entity = $f003Entities->get($platformId) ?? new F003Entity($platformId, $date);
                $productList = $this->productService->getProductListByReceiptUniqueIds(
                    $billingPlatform,
                    $logs->map(function (LogStore $log) {
                        return $log->getReceiptUniqueId();
                    })
                );

                $logs->each(function (LogStore $log) use (
                    $f003Entity,
                    $platformId,
                    $productList,
                ) {
                    $oprProductId = $productList->getReceiptToOprProductId($log->getReceiptUniqueId());
                    $productInfo = $productList->getProductInfo($oprProductId);
                    if (empty($productInfo)) {
                        return;
                    }
                    $productType = $productInfo->getProductType();
                    if (
                        ($productType === ProductType::PACK->value && $productInfo instanceof ProductPackInfo)
                        || ($productType == ProductType::PASS->value && $productInfo instanceof ProductPassInfo)
                    ) {
                        // パック商品の場合は有償通貨以外の部分の価格、パス商品の場合は商品全体の価格
                        $price = ($productType === ProductType::PACK->value)
                            ? $productInfo->getDirectPrice($platformId)
                            : $productInfo->getPrice($platformId);

                        $data = $f003Entity->getDirectData($oprProductId);
                        if ($data === null) {
                            $data = new F003DirectDataEntity(
                                $productInfo->getName(),
                                $price
                            );
                        }
                        $data->addTotalCount(1);
                        $f003Entity->addDirectData($oprProductId, $data);
                    }
                });
                $f003Entities->put($platformId, $f003Entity);
            });

        });
        return $f003Entities;
    }

    /**
     * F003Entityを元にBankF003を生成する
     *
     * @param Collection<F003Entity> $f003Entities
     * @param CarbonImmutable $now
     * @return Collection<AdmBankF003>
     */
    public function createLog(Collection $f003Entities, CarbonImmutable $now): Collection
    {
        $applicationId = $this->environmentService->getApplicationId();
        $models = $f003Entities->map(function (F003Entity $log) use ($applicationId,) {
            return $this->admBankF003Repository->createModel(
                $applicationId,
                $log->getPlatformId(), // すでに正しいplatform_id（'asb', 'ios', 'android'）が設定済み
                $log->getDate(),
                $log->getTotalSales(),
                $log->getDataJson(),
                $log->getDirectTotalSales(),
                $log->getDirectDataJson(),
                $log->getSubscriptionTotalSales(),
                $log->getSubscriptionDataJson(),
            );
        });
        $this->insertAdmBankF003s($models, $now);
        return $models;
    }

    /**
     * AdmBankF003のデータを一括挿入する
     *
     * @param Collection $admBankF003s
     * @param CarbonImmutable $now
     * @return void
     */
    public function insertAdmBankF003s(Collection $admBankF003s, CarbonImmutable $now): void {
        $this->admBankF003Repository->bulkInsert($admBankF003s, $now);
    }

    /**
     * BankF003のデータを整形する
     *
     * @param Collection<AdmBankF003> $admBankF003s
     * @return Collection<string>
     */
    public function formatDataRecords(
        string $env,
        Collection $admBankF003s,
        CarbonImmutable $now
    ): Collection {
        $applicationId = $this->environmentService->getApplicationId();
        $fluentdTag = $this->bankService->getFluentdTag($env, $applicationId, BankKPIFormatType::F003->value);
        return $admBankF003s->map(function (AdmBankF003 $admBankF003) use ($fluentdTag, $now) {
            return implode("\t", [
                $now->format('Y-m-d\TH:i:s\Z') ?? '',
                $fluentdTag,
                json_encode([
                    'app_id' => $admBankF003->getAppId(),
                    'client_id' => $this->environmentService->getClientId(),
                    'client_secret' => $this->environmentService->getClientSecret(),
                    'platform_id' => $admBankF003->getPlatformId(),
                    'date' => $admBankF003->getDate(),
                    'total_sales' => $admBankF003->getTotalSales(),
                    'data' => $admBankF003->getData(),
                    'direct_total_sales' => $admBankF003->getDirectTotalSales(),
                    'direct_data' => $admBankF003->getDirectData(),
                    'subscription_total_sales' => $admBankF003->getSubscriptionTotalSales(),
                    'subscription_data' => $admBankF003->getSubscriptionData(),
                ]),
            ]);
        });
    }
}
