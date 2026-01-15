<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Enums\Language;
use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Entities\MngInGameNoticeBundle;
use App\Domain\Resource\Mng\Entities\MngInGameNoticeEntity;
use App\Domain\Resource\Mng\Entities\MngInGameNoticeI18nEntity;
use App\Domain\Resource\Mng\Models\MngInGameNotice;
use App\Domain\Resource\Mng\Models\MngInGameNoticeI18n;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * mng_in_game_notices, mng_in_game_notices_i18nのデータを
 * ノーティス1つごとにMngInGameNoticeBundleインスタンスとしてまとめて取得するためのRepository
 */
readonly class MngInGameNoticeBundleRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    private function getMngInGameNoticeBundles(string $language, CarbonImmutable $now): Collection
    {
        return $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngInGameNoticeBundleKey($language),
            fn() => $this->createMngInGameNoticeBundles($language, $now),
        );
    }

    /**
     * 現在開催期間中のMngInGameNoticeデータ(MngInGameNoticeBundleとして1ノーティスをまとめている)を取得（キャッシュ対応）
     *
     * @param string $language
     * @param CarbonImmutable $now
     * @return Collection<string, MngInGameNoticeBundle>
     *   key: mng_in_game_notices.id, value: MngInGameNoticeBundle
     */
    public function getActiveMngInGameNoticeBundlesByLanguage(string $language, CarbonImmutable $now): Collection
    {
        $mngInGameNoticeBundles = $this->getMngInGameNoticeBundles($language, $now);

        return $mngInGameNoticeBundles->filter(function (MngInGameNoticeBundle $bundle) use ($now) {
            return $bundle->isActive($now);
        });
    }

    /**
     * @param string $language
     * @param Collection<string> $mngInGameNoticeIds
     * @param CarbonImmutable $now
     * @return Collection<string, MngInGameNoticeBundle>
     *   key: mng_in_game_notices.id, value: MngInGameNoticeBundle
     */
    public function getActiveMngInGameNoticeBundlesByLanguageAndMngInGameNoticeIds(
        string $language,
        Collection $mngInGameNoticeIds,
        CarbonImmutable $now
    ): Collection {
        $mngInGameNoticeBundles = $this->getMngInGameNoticeBundles($language, $now);

        return $mngInGameNoticeBundles->only($mngInGameNoticeIds->unique()->toArray())
            ->filter(function (MngInGameNoticeBundle $bundle) use ($now) {
                return $bundle->isActive($now);
            });
    }

    /**
     * MngInGameNoticeBundleを作成
     * MngInGameNoticeBundle = 1ノーティスごとの mng_in_game_notices, i18n データをまとめたentity
     *
     * @param string $language
     * @return Collection<string, MngInGameNoticeBundle>
     *  key: mng_in_game_notices.id, value: MngInGameNoticeBundle
     */
    private function createMngInGameNoticeBundles(string $language, CarbonImmutable $now): Collection
    {
        $cacheBaseTime = $this->mngCacheRepository->getCacheBaseTime($now);

        // 期限切れしていないMngInGameNoticeデータを取得
        $mngInGameNotices = $this->getActiveMngInGameNotices($cacheBaseTime);

        if ($mngInGameNotices->isEmpty()) {
            return collect();
        }

        $mngInGameNoticeIds = $mngInGameNotices->keys();

        // 多言語データを取得
        $mngInGameNoticeI18ns = $this->getMngInGameNoticeI18nsByLanguage($mngInGameNoticeIds, $language);

        // MngInGameNoticeBundleを作成
        $mngInGameNoticeBundles = collect();
        foreach ($mngInGameNotices as $mngInGameNotice) {
            $mngInGameNoticeId = $mngInGameNotice->getId();
            $mngInGameNoticeI18n = $mngInGameNoticeI18ns->get($mngInGameNoticeId);

            if ($mngInGameNoticeI18n === null) {
                // i18nデータが存在しない場合はスキップ
                continue;
            }

            $mngInGameNoticeBundles->put($mngInGameNoticeId, new MngInGameNoticeBundle(
                $mngInGameNotice,
                $mngInGameNoticeI18n
            ));
        }

        return $mngInGameNoticeBundles;
    }

    /**
     * 期限切れしていない有効なMngInGameNoticeデータを取得。
     * 期限切れしていて無効なデータはキャッシュに含めないようにするため。
     *
     * @return Collection<string, MngInGameNoticeEntity>
     *  key: mng_in_game_notices.id, value: MngInGameNoticeEntity
     */
    private function getActiveMngInGameNotices(CarbonImmutable $now): Collection
    {
        $result = collect();
        $models = MngInGameNotice::query()
            ->where('enable', 1)
            ->where('end_at', '>=', $now)
            ->get();
        foreach ($models as $model) {
            $entity = $model->toEntity();
            $result->put($entity->getId(), $entity);
        }

        return $result;
    }

    /**
     * 指定されたノーティスIDと言語で多言語データを取得
     *
     * @param Collection<string> $mngInGameNoticeIds
     * @param string $language
     * @return Collection<string, MngInGameNoticeI18nEntity>
     */
    private function getMngInGameNoticeI18nsByLanguage(Collection $mngInGameNoticeIds, string $language): Collection
    {
        $result = collect();
        $models = MngInGameNoticeI18n::query()
            ->whereIn('mng_in_game_notice_id', $mngInGameNoticeIds->toArray())
            ->where('language', $language)
            ->get();

        foreach ($models as $model) {
            $entity = $model->toEntity();
            $result->put($entity->getMngInGameNoticeId(), $entity);
        }

        return $result;
    }

    private function getAllCacheKeys(): Collection
    {
        $keys = collect();
        foreach (Language::cases() as $language) {
            $keys->push(CacheKeyUtil::getMngInGameNoticeBundleKey($language->value));
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
