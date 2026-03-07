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

    public function testIsTransactionCompleted_正常系_トランザクションが完了済みの場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $this->setUsrUserId($usrUser->getId());
        $transactionId = 'transaction1';

        // 完了済みトランザクション作成
        UsrWebstoreTransaction::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'transaction_id' => $transactionId,
            'status' => WebStoreConstant::TRANSACTION_STATUS_COMPLETED,
        ]);

        // Exercise
        $result = $this->webStoreTransactionService->isTransactionCompleted($transactionId);

        // Verify
        $this->assertTrue($result);
    }

    public function testIsTransactionCompleted_正常系_トランザクションが未完了の場合(): void
    {
        // Setup
        $usrUser = UsrUser::factory()->create();
        $this->setUsrUserId($usrUser->getId());
        $transactionId = 'transaction2';

        // 未完了トランザクション作成
        UsrWebstoreTransaction::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'transaction_id' => $transactionId,
            'status' => WebStoreConstant::TRANSACTION_STATUS_PENDING,
        ]);

        // Exercise
        $result = $this->webStoreTransactionService->isTransactionCompleted($transactionId);

        // Verify
        $this->assertFalse($result);
    }

    public function testIsTransactionCompleted_異常系_トランザクションが存在しない場合(): void
    {
        // Setup
        $transactionId = 'non_existent_transaction_id';

        // Exercise & Verify
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::WEBSTORE_TRANSACTION_NOT_FOUND);

        $this->webStoreTransactionService->isTransactionCompleted($transactionId);
    }
}
