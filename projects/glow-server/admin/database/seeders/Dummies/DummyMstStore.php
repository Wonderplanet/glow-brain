<?php

namespace Database\Seeders\Dummies;

use App\Constants\ProductType;
use App\Domain\Resource\Mst\Models\MstPackI18n;
use App\Domain\Resource\Mst\Models\MstStoreProductI18n;
use App\Models\Mst\MstPack;
use App\Models\Mst\MstShopPass;
use App\Models\Mst\MstShopPassI18n;
use App\Models\Mst\MstStoreProduct;
use App\Models\Opr\OprProduct;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * 有償課金回収機能チェック用のダミーデータを生成する(コマンドは下記)
 * sail admin artisan db:seed --class="Database\Seeders\Dummies\DummyCollectCurrencyPaidSeeder"
 */
class DummyMstStore extends Seeder
{
    public int $numberOfRecords = 300;

    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $now = CarbonImmutable::now()->setTimezone('Asia/Tokyo');
        $startDate = $now->subDays(rand(1, 100))->toDateTimeString();
        $endDate = $now->addYears(10)->toDateTimeString();
        $productTypes = [ProductType::DIAMOND->value, ProductType::PACK->value, ProductType::PASS->value];
        $mstStoreProductInsertData = [];
        $mstStoreProductI18nInsertData = [];
        $oprProductInsertData = [];
        $mstPackInsertData = [];
        $mstPackI18nInsertData = [];
        $mstShopPassInsertData = [];
        $mstShopPassI18nInsertData = [];
        for ($i = 1; $i <= $this->numberOfRecords; $i++) {
            $productType = $productTypes[$i <= 100 ? 0 : ($i <= 200 ? 1 : 2)];
            $mstStoreProductId = 'mst_store_product_' . $i;
            $mstStoreProductInsertData[] = [
                'id' => $mstStoreProductId,
                'product_id_ios' => 'ios_' . $i,
                'product_id_android' => 'android_' . $i,
                'release_key' => 1,
            ];
            $mstStoreProductI18nInsertData[] = [
                'id' => $mstStoreProductId . '_i18n',
                'mst_store_product_id' => $mstStoreProductId,
                'language' => 'ja',
                'price_ios' => rand(100, 10000) / 100,
                'price_android' => rand(100, 10000) / 100,
                'release_key' => 1,
            ];
            $oprProductId = 'opr_product_' . $i;
            $oprProductInsertData[] = [
                'id' => $oprProductId,
                'mst_store_product_id' => $mstStoreProductId,
                'product_type' => $productType,
                'purchasable_count' => 5,
                'paid_amount' => $productType === 'diamond' ? 100 + $i : 0,
                'display_priority' => $i,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'release_key' => 1,
            ];

            if ($productType === 'pack') {
                $mstPackId = 'mst_pack_' . $i;
                $mstPackInsertData[] = [
                    'id' => $mstPackId,
                    'product_sub_id' => $oprProductId,
                    'discount_rate' => 0,
                    'sale_condition' => 'UserLevel',
                    'sale_condition_value' => 1,
                    'sale_hours' => 24,
                    'cost_type' => 'Cash',
                    'cost_amount' => 100,
                    'is_recommend' => 1,
                    'asset_key' => '',
                    'pack_decoration' => 'Gold',
                    'release_key' => 1,
                ];
                $mstPackI18nInsertData[] = [
                    'id' => $mstPackId . '_i18n',
                    'mst_pack_id' => $mstPackId,
                    'language' => 'ja',
                    'name' => 'パック' . $i,
                    'release_key' => 1,
                ];
            }
            if ($productType === 'pass') {
                $mstShopPassId = 'mst_shop_pass_' . $i;
                $mstShopPassInsertData[] = [
                    'id' => $mstShopPassId,
                    'opr_product_id' => $oprProductId,
                    'is_display_expiration' => 0,
                    'pass_duration_days' => 30,
                    'asset_key' => '',
                    'release_key' => 1,
                ];
                $mstShopPassI18nInsertData[] = [
                    'id' => $mstShopPassId . '_i18n',
                    'mst_shop_pass_id' => $mstShopPassId,
                    'language' => 'ja',
                    'name' => 'パス' . $i,
                    'release_key' => 1,
                ];
            }
        }
        MstStoreProduct::query()->upsert(
            $mstStoreProductInsertData,
            ['id'],
            ['product_id_ios', 'product_id_android']
        );
        MstStoreProductI18n::query()->upsert(
            $mstStoreProductI18nInsertData,
            ['id'],
            ['mst_store_product_id', 'language', 'price_ios', 'price_android']
        );
        OprProduct::query()->upsert(
            $oprProductInsertData,
            ['id'],
            ['mst_store_product_id', 'product_type', 'purchasable_count', 'paid_amount', 'display_priority', 'start_date', 'end_date']
        );
        MstPack::query()->upsert(
            $mstPackInsertData,
            ['id'],
            ['product_sub_id', 'discount_rate', 'sale_condition', 'sale_condition_value', 'sale_hours', 'cost_type', 'cost_amount', 'is_recommend', 'asset_key', 'pack_decoration']
        );
        MstPackI18n::query()->upsert(
            $mstPackI18nInsertData,
            ['id'],
            ['mst_pack_id', 'language', 'name']
        );
        MstShopPass::query()->upsert(
            $mstShopPassInsertData,
            ['id'],
            ['opr_product_id', 'is_display_expiration', 'pass_duration_days', 'asset_key']
        );
        MstShopPassI18n::query()->upsert(
            $mstShopPassI18nInsertData,
            ['id'],
            ['mst_shop_pass_id', 'language', 'name']
        );
    }
}
