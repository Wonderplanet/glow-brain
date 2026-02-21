<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Mst\Entities\MstArtworkGradeUpEntity;
use App\Domain\Resource\Mst\Models\MstArtworkGradeUp;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstArtworkGradeUpRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * 全エンティティを取得（APCuキャッシュ対応）
     * @return Collection<int, MstArtworkGradeUpEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstArtworkGradeUp::class);
    }

    /**
     * 原画IDとグレードレベルから原画個別設定を取得
     * @param string $mstArtworkId mstArtworks.id
     * @param int $gradeLevel usrArtworks.gradeLevel
     * @return MstArtworkGradeUpEntity|null
     */
    public function getByMstArtworkIdAndGradeLevel(string $mstArtworkId, int $gradeLevel): ?MstArtworkGradeUpEntity
    {
        return $this->getAll()->first(function (MstArtworkGradeUpEntity $entity) use ($mstArtworkId, $gradeLevel) {
            return $entity->getMstArtworkId() === $mstArtworkId
                && $entity->getGradeLevel() === $gradeLevel;
        });
    }

    /**
     * シリーズ+レアリティ+グレードレベルからデフォルト設定を取得
     * @param string $mstSeriesId mstArtworks.mstSeriesId
     * @param string $rarity mstArtworks.rarity
     * @param int $gradeLevel usrArtworks.gradeLevel
     * @return MstArtworkGradeUpEntity|null
     */
    public function getBySeriesRarityAndGradeLevel(
        string $mstSeriesId,
        string $rarity,
        int $gradeLevel
    ): ?MstArtworkGradeUpEntity {
        return $this->getAll()->first(
            function (MstArtworkGradeUpEntity $entity) use ($mstSeriesId, $rarity, $gradeLevel) {
                return StringUtil::isNotSpecified($entity->getMstArtworkId())
                    && $entity->getMstSeriesId() === $mstSeriesId
                    && $entity->getRarity() === $rarity
                    && $entity->getGradeLevel() === $gradeLevel;
            }
        );
    }
}
