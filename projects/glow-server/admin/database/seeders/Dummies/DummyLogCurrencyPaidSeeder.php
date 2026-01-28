<?php

namespace Database\Seeders\Dummies;

use App\Domain\Resource\Enums\LogCurrencyPaidTriggerType;
use App\Models\Log\LogCurrencyPaid;
use App\Models\Usr\UsrStoreProductHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * ダミーのLogCurrencyPaidを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyLogCurrencyPaidSeeder"
 */
class DummyLogCurrencyPaidSeeder extends Seeder
{
    public int $numberOfRecords = 20000;
    public ?CarbonImmutable $start = null;
    public ?CarbonImmutable $end = null;

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        ini_set('memory_limit', '2G');
        $now = CarbonImmutable::now()->setTimezone('Asia/Tokyo');
        if ($this->start === null || $this->end === null) {
            $this->start = $now->subHour()->startOfHour();
            $this->end = $this->start->copy()->endOfHour();
        }
        $currencyCodes = ['USD', 'JPY', 'EUR'];
        $platforms = [CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::OS_PLATFORM_ANDROID];
        $billingPlatforms = ['AppStore', 'GooglePlay'];
        $triggerTypes = [
            LogCurrencyPaidTriggerType::SHOP->value,
            LogCurrencyPaidTriggerType::GACHA->value,
            LogCurrencyPaidTriggerType::CONSUMPTION->value,
        ];
        $triggerIds = ['trigger_1', 'trigger_2', 'trigger_3', 'trigger_4', 'trigger_5'];

        for ($i = 1; $i <= $this->numberOfRecords; $i++) {
            $oprProductNum = rand(0, 100);
            $changeAmount = 100 + $oprProductNum;
            $beforeAmount = rand(100, 10000);
            $currentAmount = $beforeAmount + $changeAmount;

            $usrUserId = 'user_' . $now->format('Ymd') . $i;
            $receiptUniqueId = fake()->uuid();
            $osPlatform = $platforms[array_rand($platforms)];
            $deviceId = 'device_' . $i;
            $age = rand(18, 60);
            $oprProductId = 'opr_product_' . $oprProductNum;
            $platformProductId = 'prod_' . $i;
            $mstStoreProductId = 'mst_store_product_' . $i;
            $currencyCode = $currencyCodes[array_rand($currencyCodes)];
            $receiptBundleId = 'bundle_' . $i;
            $receiptPurchaseToken = 'purchase_token_' . $i;
            $paidAmount = $changeAmount;
            $freeAmount = 0;
            $purchasePrice = rand(100, 10000) / 100;
            $pricePerAmount = rand(100, 10000) / 10000;
            $vipPoint = rand(0, 100);
            $isSandbox = 1;
            $billingPlatform = $billingPlatforms[array_rand($billingPlatforms)];
            // created_atを指定すると9時間マイナスされていたので補正
            $createdAt = CarbonImmutable::createFromTimestamp(
                rand($this->start->timestamp, $this->end->timestamp)
            )->addHours(9)->toDateTimeString();
            $updatedAt = $now->toDateTimeString();
            $usrStoreProductHistoryInsertData[] = [
                'id' => fake()->uuid(),
                'receipt_unique_id' => $receiptUniqueId,
                'os_platform' => $osPlatform,
                'usr_user_id' => $usrUserId,
                'device_id' => $deviceId,
                'age' => $age,
                'product_sub_id' => $oprProductId,
                'platform_product_id' => $platformProductId,
                'mst_store_product_id' => $mstStoreProductId,
                'currency_code' => $currencyCode,
                'receipt_bundle_id' => $receiptBundleId,
                'receipt_purchase_token' => $receiptPurchaseToken,
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

            $logCurrencyPaidInsertBase = [
                'id' => fake()->uuid(),
                'seq_no' => $i,
                'usr_user_id' => $usrUserId,
                'currency_paid_id' => fake()->uuid(),
                'receipt_unique_id' => $receiptUniqueId,
                'is_sandbox' => $isSandbox,
                'query' => LogCurrencyPaid::QUERY_INSERT,
                'purchase_price' => $purchasePrice,
                'purchase_amount' => $paidAmount,
                'price_per_amount' => $pricePerAmount,
                'vip_point' => $vipPoint,
                'currency_code' => $currencyCode,
                'before_amount' => $beforeAmount,
                'change_amount' => $changeAmount,
                'current_amount' => $currentAmount,
                'os_platform' => $osPlatform,
                'billing_platform' => $billingPlatform,
                'trigger_type' => $triggerTypes[array_rand($triggerTypes)],
                'trigger_id' => $triggerIds[array_rand($triggerIds)],
                'trigger_name' => 'Trigger ' . rand(1, 10),
                'trigger_detail' => json_encode(['detail' => 'example']),
                'request_id_type' => 'type_' . rand(1, 5),
                'request_id' => 'req_' . $i,
                'nginx_request_id' => 'nginx_req_' . $i,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];
            // 有償通貨追加
            $logCurrencyPaidInsertData[] = $logCurrencyPaidInsertBase;

            // 有償通貨消費
            for ($updateCount = rand(0, 3); $updateCount > 0; $updateCount--) {
                $logCurrencyPaidInsertBase['id'] = fake()->uuid();
                $logCurrencyPaidInsertBase['query'] = LogCurrencyPaid::QUERY_UPDATE;
                $logCurrencyPaidInsertBase['before_amount'] = $logCurrencyPaidInsertBase['current_amount'];
                $logCurrencyPaidInsertBase['change_amount'] = -rand(20, 30);
                $logCurrencyPaidInsertBase['current_amount'] = $logCurrencyPaidInsertBase['before_amount'] + $logCurrencyPaidInsertBase['change_amount'];
                $logCurrencyPaidInsertData[] = $logCurrencyPaidInsertBase;
            }

            if ($i % 500 === 0 || $i === $this->numberOfRecords) {
                UsrStoreProductHistory::query()->insert($usrStoreProductHistoryInsertData);
                $usrStoreProductHistoryInsertData = [];
                LogCurrencyPaid::query()->insert($logCurrencyPaidInsertData);
                $logCurrencyPaidInsertData = [];
            }
        }
    }
}
