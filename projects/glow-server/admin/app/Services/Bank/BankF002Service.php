<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Constants\BankKPIConstant;
use App\Constants\BankKPIFormatType;
use App\Constants\CurrencyCode;
use App\Constants\ProductType;
use App\Models\Adm\AdmBankF002;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Log\LogStore;
use App\Models\Opr\OprProduct;
use App\Models\Usr\UsrCurrencyPaid;
use App\Repositories\Adm\AdmBankF002Repository;
use App\Services\EnvironmentService;
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

        $models = $logCurrencyPaids->map(function (LogCurrencyPaid $log) use (
            $fluentdTag,
            $applicationId,
            $usrCurrencyPaids,
            $foreignCurrencyMonthlyRateEntities,
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
                $buyAmount = (float)($log->getPurchasePrice()) * $rate;
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

        $models = $logStores->map(function (LogStore $log) use (
            $fluentdTag,
            $applicationId,
            $oprProducts,
            $foreignCurrencyMonthlyRateEntity,
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
            if ($productType === ProductType::PACK->value || $productType === ProductType::PASS->value) {
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
}
