<?php

namespace Tests\Feature\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Models\UsrWebstoreTransaction;
use App\Domain\Shop\Services\WebStoreTransactionService;
use App\Domain\User\Models\UsrUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebStoreTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private WebStoreTransactionService $webStoreTransactionService;

    public function setUp(): void
    {
        parent::setUp();
        $this->webStoreTransactionService = $this->app->make(WebStoreTransactionService::class);
    }

    public function testIssueTransactionId_正常系_トランザクションIDが発行され保存されること(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $usrUserId = $usrUser->getId();
        $this->setUsrUserId($usrUserId);
        $isSandbox = false;

        // Exercise
        $transactionId = $this->webStoreTransactionService->issueTransactionId($usrUserId, $isSandbox);
        $this->saveAll();

        // Verify
        $this->assertDatabaseHas('usr_webstore_transactions', [
            'usr_user_id' => $usrUserId,
            'transaction_id' => $transactionId,
            'status' => WebStoreConstant::TRANSACTION_STATUS_PENDING,
            'is_sandbox' => 0,
        ]);
    }

    public function testVerifyTransactionExists_正常系_トランザクションが存在する場合は例外が投げられないこと(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $this->setUsrUserId($usrUser->getId());
        $transactionId = 'transaction1';

        // トランザクション作成
        UsrWebstoreTransaction::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'transaction_id' => $transactionId,
        ]);

        // Exercise
        $this->webStoreTransactionService->verifyTransactionExists($transactionId);
        $this->saveAll();

        // アサーションなしで正常終了すればOK
        $this->assertTrue(true);
    }

    public function testVerifyTransactionExists_異常系_トランザクションが存在しない場合(): void
    {
        // Setup
        $transactionId = 'non_existent_transaction_id';

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_TRANSACTION_NOT_FOUND);

        $this->webStoreTransactionService->verifyTransactionExists($transactionId);
    }
}
