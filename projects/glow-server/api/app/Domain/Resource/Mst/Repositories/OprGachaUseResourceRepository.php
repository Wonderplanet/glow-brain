<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\OprGachaUseResourceEntity;
use App\Domain\Resource\Mst\Models\OprGachaUseResource;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class OprGachaUseResourceRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprGachaUseResourceEntity>
     * @throws GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(OprGachaUseResource::class);
    }

    /**
     * @param string $oprGachaId
     * @param string $costType
     * @param int $drawCount
     * @param bool $isThrowError
     *
     * @throws GameException
     */
    public function getByIdAndCostTypeAndDrawCount(
        string $oprGachaId,
        string $costType,
        int $drawCount,
        bool $isThrowError = false
    ): ?OprGachaUseResourceEntity {
        $entities = $this->getAll()->filter(
            function (OprGachaUseResourceEntity $entity) use ($oprGachaId, $costType, $drawCount) {
                return $entity->getOprGachaId() === $oprGachaId
                    && $entity->getCostType()->value === $costType
                    && $entity->getDrawCount() === $drawCount;
            }
        );

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_gacha_use_resources record is not found. (opr_gacha_id: %s)', $oprGachaId),
            );
        }

        // opr_gacha_id, cost_type, draw_count の組み合わせで一意になるテーブルなので、最初の1件を返す
        return $entities->first();
    }

    public function getByOprGachaId(string $oprGachaId): Collection
    {
        return $this->getAll()->filter(
            function (OprGachaUseResourceEntity $entity) use ($oprGachaId) {
                return $entity->getOprGachaId() === $oprGachaId;
            }
        );
    }
}
