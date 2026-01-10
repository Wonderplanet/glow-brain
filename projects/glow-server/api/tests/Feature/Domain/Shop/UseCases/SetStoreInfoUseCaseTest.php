<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Shop\UseCases;

use App\Domain\Shop\UseCases\SetStoreInfoUseCase;
use App\Domain\User\Models\UsrUserProfile;
use Tests\Support\Entities\CurrentUser;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Delegators\BillingDelegator;
use WonderPlanet\Domain\Billing\Models\UsrStoreInfo;

class SetStoreInfoUseCaseTest extends TestCase
{
    private SetStoreInfoUseCase $useCase;
    private BillingDelegator $billingDelegator;

    public function setUp(): void
    {
        parent::setUp();

        $this->useCase = $this->app->make(SetStoreInfoUseCase::class);
        $this->billingDelegator = $this->app->make(BillingDelegator::class);
    }

    public function test_ショップ情報を設定する()
    {
        // Setup
        $this->fixTime('2024-07-01 00:00:00');

        $usrUserId = $this->createUsrUser()->getId();
        $currentUser = new CurrentUser($usrUserId);

        $usrStoreInfo = UsrStoreInfo::factory()->create([
            'usr_user_id' => $usrUserId,
            'age' => 0,
            'paid_price' => 0,
            'renotify_at' => null,
        ]);

        $intBirthDate = 20040701; // 固定時間と比較して20歳になる日付

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
            'birth_date' => '', // 初期化時は空文字
        ]);

        // Exercise
        $result = $this->useCase->exec($currentUser, $intBirthDate);

        // Verify
        $usrStoreInfo->refresh();
        $this->assertEquals($usrUserId, $usrStoreInfo->usr_user_id);
        $this->assertEquals(20, $usrStoreInfo->age);
        $this->assertEquals(0, $usrStoreInfo->paid_price);
        $this->assertNull($usrStoreInfo->renotify_at);
    }
}
