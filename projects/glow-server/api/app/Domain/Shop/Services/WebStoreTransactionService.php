<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Repositories\UsrWebstoreTransactionRepository;
use Ramsey\Uuid\Uuid;

/**
 * WebStoreトランザクション管理サービス
 *
 * WebStore決済トランザクションの以下の責務を担当：
 * - トランザクションID発行（W2: payment_validation）
 * - トランザクション存在確認（W5: order_paid）
 */
class WebStoreTransactionService
{
    public function __construct(
        private readonly UsrWebstoreTransactionRepository $usrWebstoreTransactionRepository,
    ) {
    }

    /**
     * トランザクションIDを発行し、usr_webstore_transactionsに保存
     *
     * @param string $usrUserId ユーザーID
     * @param bool $isSandbox テスト決済フラグ
     * @return string トランザクションID（UUID v4）
     */
    public function issueTransactionId(string $usrUserId, bool $isSandbox): string
    {
        // UUID v4を生成
        $transactionId = (string) Uuid::uuid4();

        // usr_webstore_transactionsに保存（status: pending）
        $this->usrWebstoreTransactionRepository->create(
            $usrUserId,
            $transactionId,
            WebStoreConstant::TRANSACTION_STATUS_PENDING,
            $isSandbox,
        );

        return $transactionId;
    }

    /**
     * トランザクションIDの存在を確認
     *
     * @param string $transactionId トランザクションID
     * @return void
     * @throws GameException トランザクションが存在しない場合
     */
    public function verifyTransactionExists(string $transactionId): void
    {
        $transaction = $this->usrWebstoreTransactionRepository->findByTransactionId($transactionId);

        if (is_null($transaction)) {
            throw new GameException(ErrorCode::WEBSTORE_TRANSACTION_NOT_FOUND);
        }
    }
}
