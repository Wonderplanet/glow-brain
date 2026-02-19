<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Models\UsrCurrencyFree;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencyFreeRepository;

class UsrCurrencyFreeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UsrCurrencyFreeRepository $usrCurrencyFreeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencyFreeRepository = $this->app->make(UsrCurrencyFreeRepository::class);
    }

    #[Test]
    public function insertFreeCurrency_無償一次通貨が登録されること()
    {
        // Exercise
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Verify
        // 登録情報の確認
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(100, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(110, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(120, $usrCurrencyFree->reward_amount);
    }

    #[Test]
    public function incrementFreeCurrency_無償一次通貨が追加されること()
    {
        // Setup
        // 登録済み状態にする
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $this->usrCurrencyFreeRepository->incrementFreeCurrency('1', 10, 20, 30);

        // Verify
        // 追加されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(110, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(130, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(150, $usrCurrencyFree->reward_amount);
    }

    #[Test]
    public function decrementFreeCurrency_無償一次通貨が減算されること()
    {
        // Setup
        // 登録済み状態にする
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);

        // Exercise
        $this->usrCurrencyFreeRepository->decrementFreeCurrency('1', 10, 20, 30);

        // Verify
        // 減算されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertEquals(90, $usrCurrencyFree->ingame_amount);
        $this->assertEquals(90, $usrCurrencyFree->bonus_amount);
        $this->assertEquals(90, $usrCurrencyFree->reward_amount);
    }

    public function softDeleteByUserId_論理削除する()
    {
        // Setup
        // 登録済み状態にする
        $this->usrCurrencyFreeRepository->insertFreeCurrency('1', 100, 110, 120);
        $this->usrCurrencyFreeRepository->insertFreeCurrency('2', 100, 110, 120);

        // Exercise
        $this->usrCurrencyFreeRepository->softDeleteByUserId('1');

        // Verify
        // 論理削除されていること
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('1');
        $this->assertNull($usrCurrencyFree);

        // レコードが存在すること
        $usrCurrencyFree = UsrCurrencyFree::withTrashed()->where('usr_user_id', '1')->first();
        $this->assertEquals('1', $usrCurrencyFree->usr_user_id);
        $this->assertNotNull($usrCurrencyFree->deleted_at);

        // 別のユーザー情報は削除されていないこと
        $usrCurrencyFree = $this->usrCurrencyFreeRepository->findByUserId('2');
        $this->assertEquals('2', $usrCurrencyFree->usr_user_id);
        $this->assertNull($usrCurrencyFree->deleted_at);
    }
}
