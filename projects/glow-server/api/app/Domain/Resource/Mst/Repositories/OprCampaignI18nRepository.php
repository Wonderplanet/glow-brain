<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\OprCampaignI18nEntity;
use App\Domain\Resource\Mst\Models\OprCampaignI18n as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class OprCampaignI18nRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprCampaignI18nEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param Collection $oprCampaignIds
     * @param string     $language
     * @return Collection<\App\Domain\Resource\Mst\Entities\OprCampaignI18nEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByOprCampaignIds(Collection $oprCampaignIds, string $language): Collection
    {
        return $this->getAll()->filter(function (OprCampaignI18nEntity $entity) use ($oprCampaignIds, $language) {
            return $oprCampaignIds->contains($entity->getOprCampaignId()) && $entity->getLanguage() === $language;
        })->keyBy(function ($entity): string {
            return $entity->getOprCampaignId();
        });
    }
}
