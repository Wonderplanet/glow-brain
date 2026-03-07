<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Item\Enums\ItemType;
use App\Domain\Resource\Mst\Entities\MstItemEntity as Entity;
use App\Domain\Resource\Mst\Models\MstItem;
use App\Domain\Unit\Enums\UnitColorType;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstItemRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstItem::class);
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<string, Entity> key: id
     */
    public function getByIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->getAll()->only($ids->toArray());
    }

    /**
     * @param string $id
     * @param CarbonImmutable $now
     * @return Entity|null
     */
    public function getActiveItemById(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->get($id);

        if ($entity !== null && !$now->between($entity->getStartDate(), $entity->getEndDate())) {
            $entity = null;
        }

        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('mst_items record is not found. (id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * @param Collection<string> $ids
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getActiveItemsById(Collection $ids, CarbonImmutable $now, bool $isThrowError = false): Collection
    {
        $targetIds = $ids->unique();

        $entities = $this->getAll()->only($targetIds->toArray())
            ->filter(function (Entity $entity) use ($now) {
                return $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if (
            $isThrowError
            && $targetIds->count() !== $entities->count()
        ) {
            $missingIds = $targetIds->diff($entities->keys());
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items records are not found. (ids: %s)',
                    implode(', ', $missingIds->toArray())
                ),
            );
        }

        return $entities;
    }

    /**
     * アイテムタイプに該当するアイテムを取得する
     * @param string $itemType
     * @param CarbonImmutable $now
     * @return Collection
     */
    public function getActiveItemsByItemType(string $itemType, CarbonImmutable $now): Collection
    {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $now) {
                return $entity->getItemType() === $itemType
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });
    }

    /**
     * アイテムタイプと効果値に該当するアイテムを取得する
     * @param string $itemType
     * @param string $effectValue
     * @param CarbonImmutable $now
     * @return Collection
     */
    public function getActiveItemsByItemTypeAndEffectValue(
        string $itemType,
        string $effectValue,
        CarbonImmutable $now
    ): Collection {
        return $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $effectValue, $now) {
                return $entity->getItemType() === $itemType
                    && $entity->getEffectValue() === $effectValue
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });
    }

    /**
     * ランクアップ用アイテムを属性指定で取得する
     * @param string $color
     * @param CarbonImmutable $now
     * @param bool $isThrowError
     * @return Entity
     * @throws GameException
     */
    public function getRankUpMaterialByColor(string $color, CarbonImmutable $now, bool $isThrowError = false): Entity
    {
        $entities = $this->getActiveItemsByItemTypeAndEffectValue(ItemType::RANK_UP_MATERIAL->value, $color, $now);
        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, effectValue: %s)',
                    ItemType::RANK_UP_MATERIAL->value,
                    $color
                ),
            );
        }
        return $entities->first();
    }

    /**
     * ランクアップ用アイテムの全属性を取得する
     * @param \Carbon\CarbonImmutable $now
     * @return \Illuminate\Support\Collection
     */
    public function getRankUpMaterials(CarbonImmutable $now): Collection
    {
        $unitColorTypes = collect(UnitColorType::cases())->mapWithKeys(
            fn($case) => [$case->value => true]
        );

        return $this->getAll()
            ->filter(function (Entity $entity) use ($now, $unitColorTypes) {
                return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                    && $unitColorTypes->has($entity->getEffectValue())
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });
    }

    /**
     * 指定したアイテムタイプとレアリティに該当するアイテムを取得する
     *
     * 取得例：レアリティSSRの選択かけらボックスを取得
     */
    public function getByTypeAndRarity(
        string $itemType,
        string $rarity,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $rarity, $now) {
                return $entity->getItemType() === $itemType
                    && $entity->getRarity() === $rarity
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, rarity: %s)',
                    $itemType,
                    $rarity
                ),
            );
        }

        return $entities->first();
    }

    public function getRankUpMemoryFragments(
        CarbonImmutable $now,
    ): Collection {
        return $this->masterRepository->getByColumn(
            MstItem::class,
            'type',
            ItemType::RANK_UP_MEMORY_FRAGMENT->value,
        )->filter(function (Entity $entity) use ($now) {
            return $now->between($entity->getStartDate(), $entity->getEndDate());
        });
    }

    public function getRankUpMemoryFragmentByRarity(
        string $rarity,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($rarity, $now) {
                return $entity->getItemType() === ItemType::RANK_UP_MEMORY_FRAGMENT->value
                    && $entity->getRarity() === $rarity
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, rarity: %s)',
                    ItemType::RANK_UP_MEMORY_FRAGMENT->value,
                    $rarity,
                ),
            );
        }

        return $entities->first();
    }

    /**
     * ランクアップ用キャラ個別メモリーアイテムを取得する
     */
    public function getUnitMemoryByMstUnitId(
        string $mstUnitId,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($mstUnitId, $now) {
                return $entity->getItemType() === ItemType::RANK_UP_MATERIAL->value
                    && $entity->getEffectValue() === $mstUnitId
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, effectValue: %s)',
                    ItemType::RANK_UP_MATERIAL->value,
                    $mstUnitId
                ),
            );
        }

        return $entities->first();
    }

    /**
     * アイテムタイプとシリーズIDに該当するアイテムを取得する
     * @param string $itemType
     * @param string $mstSeriesId
     * @param CarbonImmutable $now
     * @param bool $isThrowError
     * @return Collection<string, Entity>
     * @throws GameException
     */
    public function getByItemTypeAndMstSeriesId(
        string $itemType,
        string $mstSeriesId,
        CarbonImmutable $now,
        bool $isThrowError = true,
    ): Collection {
        $entities = $this->getAll()
            ->filter(function (Entity $entity) use ($itemType, $mstSeriesId, $now) {
                return $entity->getItemType() === $itemType
                    && $entity->getMstSeriesId() === $mstSeriesId
                    && $now->between($entity->getStartDate(), $entity->getEndDate());
            });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_items record is not found. (type: %s, mstSeriesId: %s)',
                    $itemType,
                    $mstSeriesId
                ),
            );
        }

        return $entities;
    }
}
