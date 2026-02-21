<?php

declare(strict_types=1);

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Models\UsrItemTrade;
use App\Domain\Item\Models\UsrItemTradeInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrItemTradeRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrItemTrade::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrItemTrade $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_item_id' => $model->getMstItemId(),
                'trade_amount' => $model->getTradeAmount(),
                'reset_trade_amount' => $model->getResetTradeAmount(),
                'trade_amount_reset_at' => $model->getTradeAmountResetAt(),
            ];
        })->toArray();

        UsrItemTrade::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_item_id'],
            ['trade_amount', 'reset_trade_amount', 'trade_amount_reset_at'],
        );
    }

    public function create(
        string $usrUserId,
        string $mstItemId,
        CarbonImmutable $now,
    ): UsrItemTradeInterface {
        $model = new UsrItemTrade();

        $model->usr_user_id = $usrUserId;
        $model->mst_item_id = $mstItemId;
        $model->trade_amount = 0;
        $model->reset_trade_amount = 0;
        $model->trade_amount_reset_at = $now->toDateTimeString();

        $this->syncModel($model);

        return $model;
    }

    public function getByMstItemId(string $usrUserId, string $mstItemId): ?UsrItemTradeInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_item_id',
            $mstItemId,
            function () use ($usrUserId, $mstItemId) {
                return UsrItemTrade::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_item_id', $mstItemId)
                    ->first();
            },
        );
    }

    public function getOrCreateByMstItemId(
        string $usrUserId,
        string $mstItemId,
        CarbonImmutable $now,
    ): UsrItemTradeInterface {
        $usrItemTrade = $this->getByMstItemId($usrUserId, $mstItemId);
        if ($usrItemTrade === null) {
            $usrItemTrade = $this->create($usrUserId, $mstItemId, $now);
        }

        return $usrItemTrade;
    }

    /**
     * @return Collection<UsrItemTradeInterface>
     */
    public function getList(string $userId): Collection
    {

        return $this->cachedGetAll($userId);
    }
}
