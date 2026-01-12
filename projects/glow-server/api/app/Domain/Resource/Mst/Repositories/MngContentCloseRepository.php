<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Entities\MngContentCloseEntity;
use App\Domain\Resource\Mng\Models\MngContentClose;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class MngContentCloseRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * 有効なコンテンツクローズ一覧を取得
     * （is_valid = 1 のみ、時刻に関係なく全て含める）
     */
    public function findActiveList(): Collection
    {
        return $this->getMngContentClosesIsValid()
            ->values();
    }

    /**
     * 指定されたコンテンツタイプの現在アクティブなコンテンツクローズ一覧を取得
     * （is_valid = 1 かつ start_at <= now <= end_at）
     */
    public function findCurrentActiveListByContentType(string $contentType, CarbonImmutable $now): Collection
    {
        return $this->getMngContentClosesIsValid()
            ->filter(fn(MngContentCloseEntity $entity) =>
                $entity->getContentType() === $contentType &&
                $entity->getStartAt() <= $now &&
                $entity->getEndAt() >= $now &&
                $entity->getContentId() === null) // 全コンテンツ対象
            ->values();
    }

    /**
     * 指定されたコンテンツタイプ・IDの現在アクティブなコンテンツクローズ一覧を取得
     * （is_valid = 1 かつ start_at <= now <= end_at）
     * content_id が null の場合は全コンテンツ対象、指定されている場合は特定コンテンツのみ対象
     */
    public function findCurrentActiveListByContentTypeAndId(
        string $contentType,
        string $contentId,
        CarbonImmutable $now
    ): Collection {
        return $this->getMngContentClosesIsValid()
            ->filter(fn(MngContentCloseEntity $entity) =>
                $entity->getContentType() === $contentType &&
                $entity->getStartAt() <= $now &&
                $entity->getEndAt() >= $now &&
                (
                    $entity->getContentId() === $contentId // 特定コンテンツ対象
                ))
            ->values();
    }

    /**
     * 有効なMngContentCloseEntityのコレクションを取得（キャッシュ利用）
     * is_valid = 1 のみをキャッシュする
     */
    private function getMngContentClosesIsValid(): Collection
    {
        return $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngContentCloseKey(),
            fn() => $this->createMngContentClosesIsValid(),
        );
    }

    /**
     * 有効なMngContentCloseEntityのコレクションを作成
     * is_valid = 1 のみを含める
     *
     * @return Collection<MngContentCloseEntity>
     */
    private function createMngContentClosesIsValid(): Collection
    {
        return MngContentClose::where('is_valid', 1)
            ->get()
            ->map(fn (MngContentClose $model) => $model->toEntity());
    }

    /**
     * キャッシュを削除
     */
    public function deleteAllCache(): void
    {
        $this->mngCacheRepository->deleteCache(
            CacheKeyUtil::getMngContentCloseKey()
        );
    }
}
