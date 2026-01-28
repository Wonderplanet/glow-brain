<?php

namespace Database\Seeders\Dummies;

use App\Domain\Resource\Enums\LogStoreTriggerType;
use App\Models\Log\LogStore;
use App\Models\Usr\UsrStoreProductHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

/**
 * ダミーのLogStoreを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyLogStoreSeeder"
 */
class DummyLogStoreSeeder extends Seeder
{
    public int $numberOfRecords = 20000;
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
        $platforms = [CurrencyConstants::OS_PLATFORM_IOS, CurrencyConstants::OS_PLATFORM_ANDROID];
        $billingPlatforms = ['AppStore', 'GooglePlay'];
        $currencyCodes = ['USD', 'JPY', 'EUR'];
        $triggerTypes = [LogStoreTriggerType::SHOP->value];
        $triggerIds = ['trigger_1', 'trigger_2', 'trigger_3'];

        for ($i = 1; $i <= $this->numberOfRecords; $i++) {
            $usrUserId = 'user_' . $now->format('Ymd') . $i;
            $receiptUniqueId = fake()->uuid();
            $osPlatform = $platforms[array_rand($platforms)];
            $deviceId = 'device_' . $i;
            $age = rand(18, 60);
            $oprProductId = 'opr_product_' . rand(101, 300);
            $platformProductId = 'prod_' . $i;
            $mstStoreProductId = 'mst_prod_' . $i;
            $currencyCode = $currencyCodes[array_rand($currencyCodes)];
            $receiptBundleId = 'bundle_' . $i;
            $receiptPurchaseToken = 'purchase_token_' . $i;
            $paidAmount = rand(1, 10000);
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
            $logStoreInsertData[] = [
                'id' => fake()->uuid(),
                'seq_no' => $i,
                'usr_user_id' => $usrUserId,
                'platform_product_id' => $platformProductId,
                'mst_store_product_id' => $mstStoreProductId,
                'product_sub_id' => $oprProductId,
                'product_sub_name' => 'Product ' . $i,
                'raw_receipt' => fake()->text(200),
                'raw_price_string' => '$' . rand(1, 1000) . '.00',
                'currency_code' => $currencyCode,
                'receipt_unique_id' => $receiptUniqueId,
                'receipt_bundle_id' => $receiptBundleId,
                'os_platform' => $osPlatform,
                'billing_platform' => $billingPlatform,
                'device_id' => $deviceId,
                'age' => $age,
                'paid_amount' => $paidAmount,
                'free_amount' => $freeAmount,
                'purchase_price' => $purchasePrice,
                'price_per_amount' => $pricePerAmount,
                'vip_point' => $vipPoint,
                'is_sandbox' => $isSandbox,
                'trigger_type' => $triggerTypes[0],
                'trigger_id' => $triggerIds[array_rand($triggerIds)],
                'trigger_name' => 'Trigger ' . rand(1, 10),
                'trigger_detail' => json_encode(['detail' => 'example detail']),
                'request_id_type' => 'type_' . rand(1, 5),
                'request_id' => 'req_' . $i,
                'nginx_request_id' => 'nginx_req_' . $i,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            if ($i % 1000 === 0 || $i === $this->numberOfRecords) {
                UsrStoreProductHistory::query()->insert($usrStoreProductHistoryInsertData);
                $usrStoreProductHistoryInsertData = [];
                LogStore::query()->insert($logStoreInsertData);
                $logStoreInsertData = [];
            }
        }
    }
}
