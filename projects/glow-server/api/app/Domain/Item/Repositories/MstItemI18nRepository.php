<?php

declare(strict_types=1);

namespace App\Domain\Item\Repositories;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstItemI18n;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstItemI18nRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstItemI18nEntity>
     * @throws GameException
     */
    public function getAll(): Collection
    {
        return $this->masterRepository->get(MstItemI18n::class);
    }

    /**
     * @param string $language
     *
     * @return Collection
     * @throws GameException
     */
    public function getByLanguage(string $language): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($language) {
            return $entity->getLanguage() === $language;
        });
    }
}
