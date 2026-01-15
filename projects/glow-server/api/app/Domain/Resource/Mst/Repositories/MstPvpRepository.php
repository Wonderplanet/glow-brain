<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Pvp\Constants\PvpConstant;
use App\Domain\Resource\Mst\Entities\MstPvpEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPvp as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPvpRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $id
     * @param bool $isThrowError 存在しない場合に例外を投げるかどうか
     * @return Entity|null
     * @throws GameException
     */
    private function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entity = $this->getAll()->get($id);

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_pvps record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entity;
    }

    public function getDefault(bool $isThrowError = false): ?Entity
    {
        return $this->getById(PvpConstant::DEFAULT_MST_PVP_ID, $isThrowError);
    }

    public function getDefaultOrTargetById(
        string $id,
        bool $isThrowError = false,
    ): ?Entity {
        $entity = $this->getById($id, false);
        if ($entity !== null) {
            return $entity;
        }

        return $this->getDefault($isThrowError);
    }
}
