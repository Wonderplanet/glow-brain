<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Gacha\Enums\GachaType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Models\OprGacha;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class OprGachaRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
        private Clock $clock
    ) {
    }

    /**
     * @return Collection<OprGachaEntity>
     * @throws GameException
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(OprGacha::class);
    }

    /**
     * @param CarbonImmutable $now
     *
     * @return Collection<OprGachaEntity>
     * @throws GameException
     */
    public function getActive(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            $startDate = new CarbonImmutable($entity->getStartAt());
            $endDate = new CarbonImmutable($entity->getEndAt());
            return $now->between($startDate, $endDate);
        });
    }

    /**
     * @param string $id
     *
     * @return OprGachaEntity|null
     * @throws GameException
     */
    public function getById(string $id): ?OprGachaEntity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        return $entities->first();
    }

    public function getByIdWithError(string $id): OprGachaEntity
    {
        $entity = $this->getById($id);
        if ($entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_gachas record is not found. (opr_gacha_id: %s)', $id),
            );
        }

        return $entity;
    }

    /**
     * @param string $id
     *
     * @return OprGachaEntity
     * @throws GameException
     */
    public function getActiveById(string $id): OprGachaEntity
    {
        $entity = $this->getByIdWithError($id);
        if (!$this->clock->now()->between($entity->getStartAt(), $entity->getEndAt())) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_gachas record is not active. (opr_gacha_id: %s)', $id),
            );
        }

        return $entity;
    }

    public function getByGachaType(GachaType $gachaType, bool $isThrowError = false): Collection
    {
        $entities = $this->getAll()->filter(function ($entity) use ($gachaType) {
            return $entity->getGachaType() === $gachaType;
        });

        if ($entities->isEmpty() && $isThrowError) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_gachas record is not found. (gacha_type: %s)', $gachaType->value),
            );
        }

        return $entities;
    }

    public function getByUnlockConditionType(string $unlockConditionType, CarbonImmutable $now): Collection
    {
        return $this->getActive($now)->filter(function ($entity) use ($unlockConditionType) {
            return $entity->getUnlockConditionType() === $unlockConditionType;
        });
    }
}
