<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\OprStepupGachaStepEntity;
use App\Domain\Resource\Mst\Models\OprStepupGachaStep;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class OprStepupGachaStepRepository
{
    public function __construct(
        private MasterRepository $masterRepository
    ) {
    }

    /**
     * opr_gacha_idでフィルタリングされたステップ情報を取得
     *
     * @param string $oprGachaId
     * @return Collection<OprStepupGachaStepEntity>
     * @throws GameException
     */
    private function getByOprGachaId(string $oprGachaId): Collection
    {
        return $this->masterRepository->getByColumn(OprStepupGachaStep::class, 'opr_gacha_id', $oprGachaId);
    }

    /**
     * @param string $oprGachaId
     *
     * @return Collection<OprStepupGachaStepEntity>
     * @throws GameException
     */
    public function getListByOprGachaId(string $oprGachaId): Collection
    {
        return $this->getByOprGachaId($oprGachaId)
            ->sortBy(function ($entity) {
                return $entity->getStepNumber();
            })
            ->values();
    }

    /**
     * @param string $oprGachaId
     * @param int $stepNumber
     *
     * @return OprStepupGachaStepEntity|null
     * @throws GameException
     */
    public function findByOprGachaIdStepNumber(string $oprGachaId, int $stepNumber): ?OprStepupGachaStepEntity
    {
        return $this->getByOprGachaId($oprGachaId)
            ->first(function ($entity) use ($stepNumber) {
                return $entity->getStepNumber() === $stepNumber;
            });
    }

    /**
     * @param string $oprGachaId
     * @param int $stepNumber
     * @param bool $isThrowError
     * @return OprStepupGachaStepEntity|null
     * @throws GameException
     */
    public function getByOprGachaIdStepNumber(
        string $oprGachaId,
        int $stepNumber,
        bool $isThrowError = false
    ): ?OprStepupGachaStepEntity {

        $entity = $this->findByOprGachaIdStepNumber($oprGachaId, $stepNumber);
        if ($entity === null && $isThrowError) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'opr_stepup_gacha_steps record is not found. (opr_gacha_id: %s, step_number: %d)',
                    $oprGachaId,
                    $stepNumber
                ),
            );
        }

        return $entity;
    }
}
