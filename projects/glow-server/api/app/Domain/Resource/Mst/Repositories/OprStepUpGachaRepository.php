<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\OprStepUpGachaEntity;
use App\Domain\Resource\Mst\Models\OprStepUpGacha;
use App\Infrastructure\MasterRepository;

class OprStepUpGachaRepository
{
    public function __construct(
        private MasterRepository $masterRepository
    ) {
    }

    /**
     * @param string $oprGachaId
     *
     * @return OprStepUpGachaEntity|null
     * @throws GameException
     */
    public function findByOprGachaId(string $oprGachaId): ?OprStepUpGachaEntity
    {
        return $this->masterRepository
            ->getByColumn(OprStepUpGacha::class, 'opr_gacha_id', $oprGachaId)
            ->first();
    }

    /**
     * @param string $oprGachaId
     * @param bool $isThrowError
     * @return OprStepUpGachaEntity|null
     * @throws GameException
     */
    public function getByOprGachaId(string $oprGachaId, bool $isThrowError = false): ?OprStepUpGachaEntity
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
