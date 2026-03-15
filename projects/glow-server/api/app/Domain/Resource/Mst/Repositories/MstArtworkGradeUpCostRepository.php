<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstArtworkGradeUpCostEntity;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUpCost;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstArtworkGradeUpCostRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * [mstArtworkGradeUpId] => Collection<MstArtworkGradeUpCostEntity> 形式でインデックス化（APCuキャッシュ対応）
     * @return Collection<string, Collection<int, MstArtworkGradeUpCostEntity>>
     */
    private function getGradeUpIdToCostsMap(): Collection
    {
        return $this->masterRepository->get(
            MstArtworkGradeUpCost::class,
            fn(Collection $entities) => $entities->groupBy(
                fn(MstArtworkGradeUpCostEntity $e) => $e->getMstArtworkGradeUpId()
            )
        );
    }

    /**
     * mst_artwork_grade_up_idからコストを取得
     * @param string $mstArtworkGradeUpId
     * @param bool $isThrowError
     * @return Collection<int, MstArtworkGradeUpCostEntity>
     * @throws GameException
     */
    public function getByMstArtworkGradeUpId(string $mstArtworkGradeUpId, bool $isThrowError = false): Collection
    {
        $entities = $this->getGradeUpIdToCostsMap()->get($mstArtworkGradeUpId, collect());

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_artwork_grade_up_costs record is not found. (mstArtworkGradeUpId: $mstArtworkGradeUpId)",
            );
        }

        return $entities;
    }
}
