<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;
use WonderPlanet\Domain\Billing\Repositories\UsrStoreInfoRepository;

class UsrStoreInfoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UsrStoreInfoRepository $usrStoreInfoRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrStoreInfoRepository = $this->app->make(UsrStoreInfoRepository::class);
    }

    #[Test]
    public function upsertStoreInfo_ショップ情報を登録する()
    {
        // Exercise
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(100, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function upsertStoreInfo_次回確認日時をnullで登録する()
    {
        // Exercise
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, null);

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(100, $usrStoreInfo->paid_price);
        $this->assertNull($usrStoreInfo->renotify_at);
    }

    #[Test]
    public function upsertStoreInfo_ショップ情報を更新する()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 21, 101, '2020-02-01 00:00:00');

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(21, $usrStoreInfo->age);
        $this->assertEquals(101, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-02-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function upsertStoreInfoAge_年齢と再通知日時情報を指定して新規登録する()
    {
        // Setup
        $usrUserId = '1';
        
        // Exercise
        $this->usrStoreInfoRepository->upsertStoreInfoAge($usrUserId, 20, '2020-01-01 00:00:00');

        // Verify
        $usrStoreInfo = UsrStoreInfo::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($usrUserId, $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function upsertStoreInfoAge_年齢と再通知日時情報を更新する()
    {
        // Setup
        $usrUserId = '1';
        $usrStoreInfo = UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 20,
            'paid_price' => 100,
            'renotify_at' => '2020-01-01 00:00:00',
        ]);

        // Exercise
        $this->usrStoreInfoRepository->upsertStoreInfoAge($usrUserId, 25, null);

        // Verify
        $usrStoreInfo->refresh();
        $this->assertEquals($usrUserId, $usrStoreInfo->usr_user_id);
        $this->assertEquals(25, $usrStoreInfo->age);
        $this->assertNull($usrStoreInfo->renotify_at);
    }

    public static function incrementPaidPriceData()
    {
        return [
            '小数点以下が4以下' => ['100.3', 200],
            '小数点以下が5以上' => ['100.5', 200],
        ];
    }

    #[Test]
    #[DataProvider('incrementPaidPriceData')]
    public function incrementPaidPrice_購入額を加算する(string $amount, int $expectedPaidPrice)
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->usrStoreInfoRepository->incrementPaidPrice('1', $amount);

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals($expectedPaidPrice, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    public static function decrementPaidPriceData()
    {
        return [
            '小数点以下が4以下' => ['100.3', 0],
            '小数点以下が5以上' => ['100.5', 0],
        ];
    }

    #[Test]
    #[dataProvider('decrementPaidPriceData')]
    public function decrementPaidPrice_購入額を減算する(string $amount, int $expectedPaidPrice)
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->usrStoreInfoRepository->decrementPaidPrice('1', $amount);

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals($expectedPaidPrice, $usrStoreInfo->paid_price);
        $this->assertEquals('2020-01-01 00:00:00', $usrStoreInfo->renotify_at);
    }

    #[Test]
    public function softDeleteByUserId_論理削除する()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');
        $this->usrStoreInfoRepository->upsertStoreInfo('2', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->usrStoreInfoRepository->softDeleteByUserId('1');

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertNull($usrStoreInfo);

        // 論理削除されていることを確認
        $usrStoreInfo = UsrStoreInfo::withTrashed()
            ->where('usr_user_id', '1')
            ->first();
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertNotNull($usrStoreInfo->deleted_at);

        // 別のユーザー情報は削除されていないことを確認
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('2');
        $this->assertEquals('2', $usrStoreInfo->usr_user_id);
        $this->assertNull($usrStoreInfo->deleted_at);
    }

    #[Test]
    public function updateTotalVipPoint_VIPポイントの合計を更新する()
    {
        // Setup
        $this->usrStoreInfoRepository->upsertStoreInfo('1', 20, 100, '2020-01-01 00:00:00');

        // Exercise
        $this->usrStoreInfoRepository->updateTotalVipPoint('1', 200);

        // Verify
        $usrStoreInfo = $this->usrStoreInfoRepository->findByUserId('1');
        $this->assertEquals('1', $usrStoreInfo->usr_user_id);
        $this->assertEquals(200, $usrStoreInfo->total_vip_point);
    }
}
