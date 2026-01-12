<?php

declare(strict_types=1);

namespace App\Domain\Message\Repositories;

use App\Domain\Message\Models\UsrTemporaryIndividualMessage;
use App\Domain\Message\Models\UsrTemporaryIndividualMessageInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrTemporaryIndividualMessageRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrTemporaryIndividualMessage::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrTemporaryIndividualMessage $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mng_message_id' => $model->getMngMessageId(),
            ];
        })->toArray();

        UsrTemporaryIndividualMessage::upsert($upsertValues, ['usr_user_id', 'mng_message_id']);
    }

    /**
     * 指定したユーザーIDとmngMessageIdに該当するデータを取得
     *
     * @param string $userId
     * @param array<string> $mngMessageIds
     * @return Collection<UsrTemporaryIndividualMessageInterface>
     */
    public function getByUserIdAndMngMessageIds(
        string $userId,
        array $mngMessageIds
    ): Collection {
        return $this->cachedGetMany(
            $userId,
            cacheCallback: function (Collection $cache) use ($mngMessageIds) {
                return $cache->filter(
                    fn(UsrTemporaryIndividualMessageInterface $model) => collect($mngMessageIds)
                        ->contains($model->getMngMessageId())
                );
            },
            expectedCount: count($mngMessageIds),
            dbCallback: function () use ($userId, $mngMessageIds) {
                return UsrTemporaryIndividualMessage::query()
                    ->where('usr_user_id', $userId)
                    ->whereIn('mng_message_id', $mngMessageIds)
                    ->get();
            }
        );
    }
}
