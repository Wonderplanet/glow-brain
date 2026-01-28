<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Entities\MngMessageBundle;
use App\Domain\Resource\Mng\Entities\MngMessageEntity;
use App\Domain\Resource\Mng\Entities\MngMessageI18nEntity;
use App\Domain\Resource\Mng\Entities\MngMessageRewardEntity;
use App\Domain\Resource\Mng\Models\MngMessage;
use App\Domain\Resource\Mng\Models\MngMessageI18n;
use App\Domain\Resource\Mng\Models\MngMessageReward;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * mng_messages, mng_messages_i18n, mng_message_rewardsのデータを
 * メッセージ1つごとにMngMessageBundleインスタンスとしてまとめて取得するためのRepository
 */
readonly class MngMessageBundleRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    private function getMngMessageBundles(string $language, CarbonImmutable $now): Collection
    {
        return $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngMessageBundleKey($language),
            fn() => $this->createMngMessageBundles($language, $now),
        );
    }

    /**
     * @param string $language
     * @param Collection<string> $mngMessageIds
     * @param CarbonImmutable $now
     * @return Collection<string, MngMessageBundle>
     *   key: mng_messages.id, value: MngMessageBundle
     */
    public function getMngMessageBundlesByLanguageAndMngMessageIds(
        string $language,
        Collection $mngMessageIds,
        CarbonImmutable $now
    ): Collection {
        $mngMessageBundles = $this->getMngMessageBundles($language, $now);

        return $mngMessageBundles->only($mngMessageIds->unique()->toArray())->filter();
    }

    /**
     * 現在開催期間中のMngMessageデータ(MngMessageBundleとして1メッセージをまとめている)を取得（キャッシュ対応）
     *
     * @param string $language
     * @param CarbonImmutable $now
     * @return Collection<string, MngMessageBundle>
     *   key: mng_messages.id, value: MngMessageBundle
     */
    public function getActiveMngMessageBundlesByLanguage(string $language, CarbonImmutable $now): Collection
    {
        $mngMessageBundles = $this->getMngMessageBundles($language, $now);

        return $mngMessageBundles->filter(function (MngMessageBundle $bundle) use ($now) {
            return $bundle->isActive($now);
        });
    }

    /**
     * MngMessageBundleを作成
     * MngMessageBundle = 1メッセージごとの mng_messages, i18n, rewards データをまとめたentity
     *
     * @param string $language
     * @return Collection<string, MngMessageBundle>
     *  key: mng_messages.id, value: MngMessageBundle
     */
    private function createMngMessageBundles(string $language, CarbonImmutable $now): Collection
    {
        $cacheBaseTime = $this->mngCacheRepository->getCacheBaseTime($now);

        // 期限切れしていないMngMessageデータを取得
        $mngMessages = $this->getNonExpiredMngMessages($cacheBaseTime);

        if ($mngMessages->isEmpty()) {
            return collect();
        }

        $mngMessageIds = $mngMessages->keys();

        // 多言語データを取得
        $mngMessageI18ns = $this->getMngMessageI18nsByLanguage($mngMessageIds, $language);

        // 報酬データを取得
        $groupedMngMessageRewards = $this->getMngMessageRewardsByMessageIds($mngMessageIds);

        // MngMessageBundleを作成
        $mngMessageBundles = collect();
        foreach ($mngMessages as $mngMessage) {
            $mngMessageId = $mngMessage->getId();
            $mngMessageI18n = $mngMessageI18ns->get($mngMessageId);
            $mngMessageRewards = $groupedMngMessageRewards->get($mngMessageId, collect());

            if ($mngMessageI18n === null) {
                // i18nデータが存在しない場合はスキップ
                continue;
            }

            $mngMessageBundles->put($mngMessageId, new MngMessageBundle(
                $mngMessage,
                $mngMessageI18n,
                $mngMessageRewards
            ));
        }

        return $mngMessageBundles;
    }

    /**
     * 期限切れしていないMngMessageデータを取得。
     * 期限切れデータはキャッシュに含めないようにするため。
     *
     * @return Collection<string, MngMessageEntity>
     *  key: mng_messages.id, value: MngMessageEntity
     */
    private function getNonExpiredMngMessages(CarbonImmutable $now): Collection
    {
        $result = collect();
        $models = MngMessage::query()->get();
        foreach ($models as $model) {
            $entity = $model->toEntity();
            if ($entity->getFinalExpiredAt() < $now) {
                continue;
            }

            $result->put($entity->getId(), $entity);
        }

        return $result;
    }
    /**
     * 指定されたメッセージIDと言語で多言語データを取得
     *
     * @param Collection<string> $mngMessageIds
     * @param string $language
     * @return Collection<string, MngMessageI18nEntity>
     */
    private function getMngMessageI18nsByLanguage(Collection $mngMessageIds, string $language): Collection
    {
        $result = collect();
        $models = MngMessageI18n::query()
            ->whereIn('mng_message_id', $mngMessageIds->toArray())
            ->where('language', $language)
            ->get();

        foreach ($models as $model) {
            $entity = $model->toEntity();
            $result->put($entity->getMngMessageId(), $entity);
        }

        return $result;
    }

    /**
     * 指定されたメッセージIDで報酬データを取得
     *
     * @param Collection<string> $mngMessageIds
     * @return Collection<string, Collection<MngMessageRewardEntity>>
     * key: mng_messages.id, value: Collection<MngMessageRewardEntity>
     * display_orderで昇順ソートされた状態で返す。
     * mngデータはAPIレスポンスとしてのみクライアントに渡しており、順序情報をクライアント側で参照できないため。
     */
    private function getMngMessageRewardsByMessageIds(Collection $mngMessageIds): Collection
    {
        $result = collect();
        $models = MngMessageReward::query()
            ->whereIn('mng_message_id', $mngMessageIds->toArray())
            ->get()
            ->sortBy('display_order');

        foreach ($models as $model) {
            $entity = $model->toEntity();
            $mngMessageId = $entity->getMngMessageId();

            if (!$result->has($mngMessageId)) {
                $result->put($mngMessageId, collect());
            }

            $result->get($mngMessageId)->push($entity);
        }

        return $result;
    }

    private function getAllCacheKeys(): Collection
    {
        $keys = collect();
        foreach (Language::cases() as $language) {
            $keys->push(CacheKeyUtil::getMngMessageBundleKey($language->value));
        }
        return $keys;
    }

    public function deleteAllCache(): void
    {
        foreach ($this->getAllCacheKeys() as $key) {
            $this->mngCacheRepository->deleteCache($key);
        }
    }
}
