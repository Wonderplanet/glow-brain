<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstArtworkFragmentEntity;
use App\Domain\Resource\Mst\Models\MstArtworkFragment;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstArtworkFragmentRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<MstArtworkFragmentEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstArtworkFragment::class);
    }

    /**
     * @param string $id
     * @return MstArtworkFragmentEntity|null
     */
    public function getById(string $id): ?MstArtworkFragmentEntity
    {
        return $this->getAll()->get($id);
    }

    /**
     * @param string $dropGroupId
     * @return Collection<MstArtworkFragmentEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByDropGroupId(string $dropGroupId): Collection
    {
        return $this->masterRepository->getByColumn(MstArtworkFragment::class, 'drop_group_id', $dropGroupId);
    }

    /**
     * @param Collection<string> $ids
     * @return Collection<MstArtworkFragmentEntity>
     */
    public function getByIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->getAll()
            ->only($ids)
            ->values();
    }

    /**
     * @param string $mstArtworkId
     * @return Collection<MstArtworkFragmentEntity>
     */
    public function getByMstArtworkId(string $mstArtworkId): Collection
    {
        return $this->masterRepository->getByColumn(MstArtworkFragment::class, 'mst_artwork_id', $mstArtworkId);
    }

    /**
     * @param Collection $mstArtworkIds
     * @return Collection<MstArtworkFragmentEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByMstArtworkIds(Collection $mstArtworkIds): Collection
    {
        $result = collect();
        $targetMstArtworkIds = $mstArtworkIds->unique();

        foreach ($targetMstArtworkIds as $mstArtworkId) {
            $result = $result->merge($this->getByMstArtworkId($mstArtworkId));
        }

        return $result;
    }
}
