<?php

namespace Database\Seeders\Dummies;

use App\Domain\Resource\Enums\LogCurrencyPaidTriggerType;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Usr\UsrCurrencyPaid;
use App\Models\Usr\UsrStoreInfo;
use App\Models\Usr\UsrStoreProductHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * 有償課金回収機能チェック用のダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyCollectCurrencyPaidSeeder"
 */
class DummyCollectCurrencyPaidSeeder extends Seeder
{
    public int $numberOfRecords = 5;
    public ?CarbonImmutable $start = null;
    public ?CarbonImmutable $end = null;

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Tokyo');
        if ($this->start === null || $this->end === null) {
            $this->start = $now->subHour()->startOfHour();
            $this->end = $this->start->copy()->endOfHour();
        }
        $currencyCodes = ['USD', 'JPY', 'EUR'];
        $platforms = [CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::OS_PLATFORM_ANDROID];
        $billingPlatforms = ['AppStore', 'GooglePlay'];
        $queries = ['purchase', 'consume'];
        $triggerTypes = [LogCurrencyPaidTriggerType::SHOP->value];
        $triggerIds = ['trigger_1', 'trigger_2', 'trigger_3', 'trigger_4', 'trigger_5'];
        $usrCurrencyPaidInsertData = [];
        $usrStoreInfoInsertData = [];
        $usrStoreProductHistoryInsertData = [];
        $logCurrencyPaidInsertData = [];

        for ($i = 1; $i <= $this->numberOfRecords; $i++) {
            $usrUserId = 'user_' . $now->format('Ymd') . $i;
            $seqNo = $now->timestamp . $i;
            $age = rand(18, 60);
            $receiptUniqueId = fake()->uuid();
            $osPlatform = $platforms[array_rand($platforms)];
            $currencyCode = $currencyCodes[array_rand($currencyCodes)];
            $triggerType = $triggerTypes[0];
            $triggerId = $triggerIds[array_rand($triggerIds)];
            $triggerName = 'Trigger ' . rand(1, 10);
            $triggerDetail = json_encode(['detail' => 'example']);
            $requestIdType = 'type_' . rand(1, 5);
            $requestId = 'req_' . $i;
            $nginxRequestId = 'nginx_req_' . $i;
            $changeAmountMax = 100;
            $beforeAmount = rand(100, 10000);
            $changeAmount = rand(-100, $changeAmountMax);
            $currentAmount = $beforeAmount + $changeAmount;
            $paidAmount = rand(1, 10000);
            $freeAmount = 0;
            $purchasePrice = rand(100, 10000) / 100;
            $purchaseAmount = rand(1, 100);
            $pricePerAmount = rand(1, 10000) / 10000;
            $vipPoint = rand(0, 100);
            $isSandbox = rand(0, 1);
            $billingPlatform = $billingPlatforms[array_rand($billingPlatforms)];
            $createdAt = CarbonImmutable::createFromTimestamp(
                rand($this->start->timestamp, $this->end->timestamp)
            )->toDateTimeString();
            $updatedAt = $now->toDateTimeString();

            $usrStoreInfoInsertData[] = [
                'id' => fake()->uuid(),
                'usr_user_id' => $usrUserId,
                'age' => $age,
                'paid_price' => rand(10000, 1000000),
                'renotify_at' => $now->addDays(100)->toDateTimeString(),
                'total_vip_point' => rand(100, 10000),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            $usrCurrencyPaidInsertData[] = [
                'id' => fake()->uuid(),
                'seq_no' => $seqNo,
                'usr_user_id' => $usrUserId,
                'left_amount' => $changeAmount,
                'purchase_price' => $purchasePrice,
                'purchase_amount' => $purchaseAmount,
                'price_per_amount' => $pricePerAmount,
                'vip_point' => $vipPoint,
                'currency_code' => $currencyCode,
                'receipt_unique_id' => $receiptUniqueId,
                'is_sandbox' => $isSandbox,
                'os_platform' => $osPlatform,
                'billing_platform' => $billingPlatform,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            $logCurrencyPaidInsertData[] = [
                'id' => fake()->uuid(),
                'seq_no' => $seqNo,
                'usr_user_id' => $usrUserId,
                'currency_paid_id' => fake()->uuid(),
                'receipt_unique_id' => $receiptUniqueId,
                'is_sandbox' => $isSandbox,
                'query' => $queries[array_rand($queries)],
                'purchase_price' => $purchasePrice,
                'purchase_amount' => $purchaseAmount,
                'price_per_amount' => $pricePerAmount,
                'vip_point' => $vipPoint,
                'currency_code' => $currencyCode,
                'before_amount' => $beforeAmount,
                'change_amount' => $changeAmount,
                'current_amount' => $currentAmount,
                'os_platform' => $osPlatform,
                'billing_platform' => $billingPlatform,
                'trigger_type' => $triggerType,
                'trigger_id' => $triggerId,
                'trigger_name' => $triggerName,
                'trigger_detail' => $triggerDetail,
                'request_id_type' => $requestIdType,
                'request_id' => $requestId,
                'nginx_request_id' => $nginxRequestId,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            if ($receiptUniqueId !== null) {
                $deviceId = 'device_' . $i;
                $productSubId = 'sub_prod_' . $i;
                $platformProductId = 'prod_' . $i;
                $mstStoreProductId = 'mst_prod_' . $i;
                $receiptBundleId = 'bundle_' . $i;
                $usrStoreProductHistoryInsertData[] = [
                    'id' => fake()->uuid(),
                    'receipt_unique_id' => $receiptUniqueId,
                    'os_platform' => $osPlatform,
                    'usr_user_id' => $usrUserId,
                    'device_id' => $deviceId,
                    'age' => $age,
                    'product_sub_id' => $productSubId,
                    'platform_product_id' => $platformProductId,
                    'mst_store_product_id' => $mstStoreProductId,
                    'currency_code' => $currencyCode,
                    'receipt_bundle_id' => $receiptBundleId,
                    'paid_amount' => $paidAmount,
                    'free_amount' => $freeAmount,
                    'purchase_price' => $purchasePrice,
                    'price_per_amount' => $pricePerAmount,
                    'vip_point' => $vipPoint,
                    'is_sandbox' => $isSandbox,
                    'billing_platform' => $billingPlatform,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            }

            if ($i % 500 === 0 || $i === $this->numberOfRecords) {
                UsrStoreProductHistory::query()->insert($usrStoreProductHistoryInsertData);
                $usrStoreProductHistoryInsertData = [];
                UsrStoreInfo::query()->upsert(
                    $usrStoreInfoInsertData,
                    ['usr_user_id'],
                    ['age', 'paid_price', 'renotify_at', 'total_vip_point', 'created_at', 'updated_at']
                );
                $usrStoreInfoInsertData = [];
                UsrCurrencyPaid::query()->insert($usrCurrencyPaidInsertData);
                $usrCurrencyPaidInsertData = [];
                LogCurrencyPaid::query()->insert($logCurrencyPaidInsertData);
                $logCurrencyPaidInsertData = [];
            }
        }
    }
}
