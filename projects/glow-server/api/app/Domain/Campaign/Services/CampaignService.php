<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Services;

use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\Language;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Repositories\OprCampaignI18nRepository;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Http\Responses\Data\OprCampaignData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class CampaignService
{
    public function __construct(
        private OprCampaignRepository $oprCampaignRepository,
        private OprCampaignI18nRepository $oprCampaignI18nRepository,
    ) {
    }

    public function getOprCampaignDataList(CarbonImmutable $now): Collection
    {
        $oprCampaigns = $this->oprCampaignRepository->getActiveCampaigns($now);

        $oprCampaignIds = $oprCampaigns->map(fn(OprCampaignEntity $entity) => $entity->getId());
        $language = request()->header(System::HEADER_LANGUAGE, Language::Ja->value);
        $oprCampaignI18ns = $this->oprCampaignI18nRepository->getByOprCampaignIds($oprCampaignIds, $language);

        $dataList = collect();
        foreach ($oprCampaigns as $oprCampaign) {
            /** @var OprCampaignEntity $oprCampaign */
            /** @var \App\Domain\Resource\Mst\Entities\OprCampaignI18nEntity|null $oprCampaignI18n */
            $oprCampaignI18n = $oprCampaignI18ns->get($oprCampaign->getId());
            if (is_null($oprCampaignI18n)) {
                continue;
            }

            $dataList->add(
                new OprCampaignData(
                    $oprCampaign->getId(),
                    $oprCampaign->getCampaignType(),
                    $oprCampaign->getTargetType(),
                    $oprCampaign->getDifficulty(),
                    $oprCampaign->getTargetIdType(),
                    $oprCampaign->getTargetId(),
                    $oprCampaign->getEffectValue(),
                    $oprCampaignI18n->getDescription(),
                    $oprCampaign->getStartAt(),
                    $oprCampaign->getEndAt(),
                )
            );
        }
        return $dataList;
    }
}
