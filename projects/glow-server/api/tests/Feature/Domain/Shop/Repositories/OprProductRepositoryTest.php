<?php

namespace Tests\Feature\Domain\Shop\Repositories;

use App\Domain\Resource\Mst\Models\MstPack;
use App\Domain\Resource\Mst\Models\OprProduct;
use App\Domain\Resource\Mst\Repositories\OprProductRepository;
use App\Domain\Shop\Enums\ProductType;
use App\Domain\Shop\Enums\SaleCondition;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class OprProductRepositoryTest extends TestCase
{
    private OprProductRepository $oprProductRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->oprProductRepository = app(OprProductRepository::class);
    }

    private function generateOprProduct(string $startDate, string $endDate): void
    {
        OprProduct::factory()->create([
            'product_type' => ProductType::DIAMOND->value,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function testGetActiveProducts_アクティブな商品を取得()
    {
        $startDate = '2024-01-01 00:00:00';
        $endDate = '2024-01-31 00:00:00';
        $this->generateOprProduct($startDate, $endDate);

        $params = [
            // 境界値(開始と同時刻)
            new CarbonImmutable($startDate),
            // 中間
            (new CarbonImmutable($startDate))->addDays(10),
            // 境界値(終了と同時刻)
            new CarbonImmutable($endDate),
        ];
        foreach ($params as $now) {
            $actual = $this->oprProductRepository->getActiveProducts($now);
            $this->assertCount(1, $actual);
        }

    }

    public function testGetActiveProducts_アクティブな商品がない()
    {
        $startDate = '2024-01-01 00:00:00';
        $endDate = '2024-01-31 00:00:00';
        $this->generateOprProduct($startDate, $endDate);

        $params = [
            // 境界値(開始と同時刻)
            (new CarbonImmutable($startDate))->subSeconds(),
            // 境界値(終了と同時刻)
            (new CarbonImmutable($endDate))->addSeconds(),
        ];
        foreach ($params as $now) {
            $actual = $this->oprProductRepository->getActiveProducts($now);
            $this->assertCount(0, $actual);
        }
    }

    /*
    public function testGetConditionPacksBySaleCondition_開放条件に一致するパックが取得できる()
    {
        $saleCondition = SaleCondition::USER_LEVEL->value;
        $params = [
            // パックではない
            [ProductType::DIAMOND->value, null],
            // sale_conditionが一致しない
            [ProductType::PACK->value, SaleCondition::STAGE_CLEAR->value],
            // 正常
            [ProductType::PACK->value, $saleCondition],
        ];
        foreach ($params as [$productType, $condition]) {
            $oprProduct = OprProduct::factory()->create([
                'product_type' => $productType,
            ])->toEntity();
            if (!is_null($condition)) {
                MstPack::factory()->create([
                    'product_sub_id' => $oprProduct->getId(),
                    'sale_condition' => $condition,
                ]);
            }
        }
        $actual = $this
            ->oprProductRepository
            ->getConditionPacksBySaleCondition($saleCondition, new CarbonImmutable());
        $this->assertCount(1, $actual);
    }
    */
}
