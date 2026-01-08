<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstExchangeLineupEntity;
use App\Domain\Resource\Mst\Models\MstExchangeLineup as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstExchangeLineupRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, MstExchangeLineupEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * IDで取得
     */
    public function getById(string $id, bool $isThrowError = false): ?MstExchangeLineupEntity
    {
        $entity = $this->getAll()->get($id);

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_exchange_lineups record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entity;
    }

    /**
     * グループIDでラインナップを取得
     *
     * @return Collection<MstExchangeLineupEntity>
     */
    public function getLineupsByGroupId(string $groupId): Collection
    {
        return $this->masterRepository
            ->getByColumn(Model::class, 'group_id', $groupId)
            ->values();
    }

    /**
     * すべてのラインナップをマップで取得
     *
     * @return Collection<string, MstExchangeLineupEntity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }
}
