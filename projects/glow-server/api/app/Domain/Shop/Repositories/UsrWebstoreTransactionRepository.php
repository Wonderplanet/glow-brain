<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrWebstoreTransaction;
use App\Domain\Shop\Models\UsrWebstoreTransactionInterface;
use Illuminate\Support\Collection;

class UsrWebstoreTransactionRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrWebstoreTransaction::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrWebstoreTransaction $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'transaction_id' => $model->getTransactionId(),
                'order_id' => $model->getOrderId(),
                'is_sandbox' => $model->isSandbox() ? 1 : 0,
                'status' => $model->getStatus(),
                'error_code' => $model->error_code,
                'item_grant_status' => $model->item_grant_status,
                'bank_status' => $model->bank_status,
                'adjust_status' => $model->adjust_status,
            ];
        })->toArray();

        UsrWebstoreTransaction::query()->upsert(
            $upsertValues,
            ['transaction_id'],
            ['order_id', 'status', 'error_code', 'item_grant_status', 'bank_status', 'adjust_status'],
        );
    }

    public function findByTransactionId(string $transactionId): ?UsrWebstoreTransactionInterface
    {
        // transaction_idでキャッシュ検索
        // UsrModelMultiCacheRepositoryのcachedGetOneWhereを使用
        return $this->cachedGetOneWhere(
            $transactionId,
            'transaction_id',
            $transactionId,
            function () use ($transactionId) {
                return UsrWebstoreTransaction::query()
                    ->where('transaction_id', $transactionId)
                    ->first();
            },
        );
    }

    public function create(
        string $usrUserId,
        string $transactionId,
        string $status,
        bool $isSandbox,
    ): UsrWebstoreTransactionInterface {
        $model = new UsrWebstoreTransaction();
        $model->usr_user_id = $usrUserId;
        $model->transaction_id = $transactionId;
        $model->status = $status;
        $model->is_sandbox = $isSandbox ? 1 : 0;

        $this->syncModel($model);

        return $model;
    }

    public function updateAdjustStatus(string $transactionId, string $adjustStatus): void
    {
        // トランザクション後に実行するので直更新
        UsrWebstoreTransaction::query()->where('transaction_id', $transactionId)->update([
            'adjust_status' => $adjustStatus,
        ]);
    }
}
