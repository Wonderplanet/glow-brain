<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstBoxGachaGroupEntity as Entity;
use App\Domain\Resource\Mst\Models\MstBoxGachaGroup as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstBoxGachaGroupRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * [mstBoxGachaId][boxLevel] => Entity 形式で取得（APCuキャッシュ対応）
     *
     * @return Collection<string, array<int, Entity>>
     */
    private function getGachaIdToBoxLevelMap(): Collection
    {
        return $this->masterRepository->get(
            Model::class,
            fn(Collection $entities) => $entities->groupBy(
                fn(Entity $e) => $e->getMstBoxGachaId()
            )->map(
                fn(Collection $group) => $group->keyBy(fn(Entity $e) => $e->getBoxLevel())->toArray()
            )->toArray()
        );
    }

    /**
     * @param string $mstBoxGachaId
     * @param int $boxLevel
     * @param bool $isThrowError
     * @return Entity|null
     * @throws GameException
     */
    public function getByMstBoxGachaIdAndBoxLevel(
        string $mstBoxGachaId,
        int $boxLevel,
        bool $isThrowError = false
    ): ?Entity {
        $indexed = $this->getGachaIdToBoxLevelMap()->toArray();
        $entity = $indexed[$mstBoxGachaId][$boxLevel] ?? null;

        if ($isThrowError && is_null($entity)) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_box_gacha_groups record is not found. (mst_box_gacha_id: %s, box_level: %d)',
                    $mstBoxGachaId,
                    $boxLevel
                ),
            );
        }

        return $entity;
    }

    /**
     * 指定したBOXガチャのbox_levelリストを取得
     *
     * @param string $mstBoxGachaId
     * @return array<int>
     */
    public function getBoxLevels(string $mstBoxGachaId): array
    {
        $indexed = $this->getGachaIdToBoxLevelMap()->toArray();
        $group = $indexed[$mstBoxGachaId] ?? [];

        if ($group === []) {
            return [];
        }

        return array_keys($group);
    }
}
