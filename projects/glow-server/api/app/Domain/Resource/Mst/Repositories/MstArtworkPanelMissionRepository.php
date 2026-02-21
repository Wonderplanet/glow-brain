<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstArtworkPanelMissionEntity as Entity;
use App\Domain\Resource\Mst\Models\MstArtworkPanelMission as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstArtworkPanelMissionRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * start_atとend_at内の対象レコードを全て取得
     * @param CarbonImmutable $now
     * @return Collection<string, Entity> key: id, value: Entity
     */
    public function getActives(CarbonImmutable $now): Collection
    {
        return $this->masterRepository->getDayActives(Model::class, $now)
            ->filter(
                function (Entity $entity) use ($now) {
                    return $now->between(
                        $entity->getStartAt(),
                        $entity->getEndAt(),
                    );
                }
            );
    }

    /**
     * start_atとend_at内の対象IDのレコードを取得
     * @param string $id
     * @param CarbonImmutable $now
     * @return Entity|null
     * @throws GameException
     */
    public function getActiveById(string $id, CarbonImmutable $now, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getActives($now)->get($id);

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_artwork_panel_missions record is not found. (id: %s)',
                    $id,
                ),
            );
        }

        return $entity;
    }
}
