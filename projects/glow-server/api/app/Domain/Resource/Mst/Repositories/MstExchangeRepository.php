<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstExchangeEntity;
use App\Domain\Resource\Mst\Models\MstExchange as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstExchangeRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<string, MstExchangeEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * IDで取得
     */
    public function getById(string $id, bool $isThrowError = false): ?MstExchangeEntity
    {
        $entity = $this->getAll()->get($id);

        if ($isThrowError && $entity === null) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_exchanges record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entity;
    }

    /**
     * すべての交換所をマップで取得
     *
     * @return Collection<string, MstExchangeEntity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll();
    }

    /**
     * 指定されたIDの交換所をマップで取得
     *
     * @param Collection<string> $ids
     * @return Collection<string, MstExchangeEntity>
     */
    public function getMapByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->toArray());
    }

    /**
     * 開催中の交換所をマップで取得
     * マスターデータの期間は Y-m-d H:i:s フォーマットのため文字列比較で判定可能
     *
     * @return Collection<string, MstExchangeEntity>
     */
    public function getActiveMap(CarbonImmutable $now): Collection
    {
        $nowString = $now->format('Y-m-d H:i:s');

        return $this->getAll()->filter(function (MstExchangeEntity $entity) use ($nowString) {
            if ($nowString < $entity->getStartAt()) {
                return false;
            }

            $endAt = $entity->getEndAt();
            if ($endAt !== null && $nowString > $endAt) {
                return false;
            }

            return true;
        });
    }
}
