<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\OprStepupGachaEntity;
use App\Domain\Resource\Mst\Models\OprStepupGacha;
use App\Infrastructure\MasterRepository;

class OprStepupGachaRepository
{
    public function __construct(
        private MasterRepository $masterRepository
    ) {
    }

    /**
     * @param string $oprGachaId
     *
     * @return OprStepupGachaEntity|null
     * @throws GameException
     */
    public function findByOprGachaId(string $oprGachaId): ?OprStepupGachaEntity
    {
        return $this->masterRepository
            ->getByColumn(OprStepupGacha::class, 'opr_gacha_id', $oprGachaId)
            ->first();
    }

    /**
     * @param string $oprGachaId
     * @param bool $isThrowError
     * @return OprStepupGachaEntity|null
     * @throws GameException
     */
    public function getByOprGachaId(string $oprGachaId, bool $isThrowError = false): ?OprStepupGachaEntity
    {
        $entity = $this->findByOprGachaId($oprGachaId);
        if ($entity === null && $isThrowError) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf('opr_stepup_gachas record is not found. (opr_gacha_id: %s)', $oprGachaId),
            );
        }

        return $entity;
    }
}
