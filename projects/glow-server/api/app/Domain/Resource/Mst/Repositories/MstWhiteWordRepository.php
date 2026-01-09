<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstWhiteWord as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstWhiteWordRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstWhiteWordEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstWhiteWordEntity>
     */
    public function getWhiteWordAll(): Collection
    {
        return $this->getAll();
    }
}
