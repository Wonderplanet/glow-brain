<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Campaign\Enums\CampaignTargetType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Common\Utils\StringUtil;
use App\Domain\Resource\Mst\Entities\MstQuestEntity;
use App\Domain\Resource\Mst\Entities\OprCampaignEntity;
use App\Domain\Resource\Mst\Models\OprCampaign as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class OprCampaignRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<OprCampaignEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param Collection<string> $ids
     * @return Collection<OprCampaignEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }
        // クエスト開始時に有効だったものをクエスト終了時に適用するなどのケースがあるため、時間ではフィルタしない
        $campaigns = $this->getAll()->filter(function ($entity) use ($ids) {
            return $ids->containsStrict($entity->getId());
        });
        return $this->useHighestValueForDuplicateCampaigns($campaigns);
    }

    /**
     * @param CarbonImmutable $now
     * @return Collection<OprCampaignEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getActiveCampaigns(CarbonImmutable $now): Collection
    {
        $campaigns = $this->getAll()->filter(function (OprCampaignEntity $entity) use ($now) {
            $startDate = new CarbonImmutable($entity->getStartAt());
            $endDate = new CarbonImmutable($entity->getEndAt());
            return $startDate->lte($now) && $endDate->gte($now);
        });
        return $this->useHighestValueForDuplicateCampaigns($campaigns);
    }

    /**
     * campaign_typeとtarget_typeが重複している場合、effect_value最大値のキャンペーンを採用する
     * スタミナの場合は1/n方式のためeffect_valueの最小値を採用する
     * @param Collection<OprCampaignEntity> $oprCampaigns
     * @return Collection<OprCampaignEntity>
     */
    private function useHighestValueForDuplicateCampaigns(Collection $oprCampaigns): Collection
    {
        if ($oprCampaigns->isEmpty()) {
            return collect();
        }

        $groupedCampaign = $oprCampaigns->groupBy(function ($oprCampaign) {
            return sprintf(
                '%s-%s-%s',
                $oprCampaign->getCampaignType(),
                $oprCampaign->getTargetType(),
                $oprCampaign->getDifficulty()
            );
        });
        return $groupedCampaign->map(function ($campaigns) {
            if ($campaigns->first()->getCampaignType() === CampaignType::STAMINA->value) {
                // スタミナの場合は最小値を選択
                return $campaigns->sortBy(fn($campaign) => $campaign->getEffectValue())->first();
            }
            return $campaigns->sortByDesc(fn($campaign) => $campaign->getEffectValue())->first();
        })->values();
    }

    /**
     * クエストに紐づくキャンペーンを取得する
     * @param CarbonImmutable $now
     * @param MstQuestEntity  $mstQuestEntity
     * @return Collection
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getActivesByMstQuest(CarbonImmutable $now, MstQuestEntity $mstQuestEntity): Collection
    {
        $oprCampaigns = $this->getActiveCampaigns($now);
        return $oprCampaigns->filter(function (OprCampaignEntity $entity) use ($mstQuestEntity) {
            $targetType = $mstQuestEntity->getQuestType() . 'Quest';
            if ($entity->getTargetType() !== $targetType) {
                // 対象のタイプが違うので除外
                return false;
            }

            if ($entity->getDifficulty() !== $mstQuestEntity->getDifficulty()) {
                // 難易度が違うので除外
                return false;
            }

            if ($entity->isTargetIdTypeQuest() && $entity->getTargetId() !== $mstQuestEntity->getId()) {
                // クエストIDが違うので除外
                return false;
            }

            if ($entity->isTargetIdTypeSeries() && $entity->getTargetId() !== $mstQuestEntity->getMstSeriesId()) {
                // シリーズIDが違うので除外
                return false;
            }
            return true;
        });
    }

    /**
     * 降臨バトルに紐づくキャンペーンを取得する
     * @param CarbonImmutable $now
     * @param string $mstAdventBattleId
     * @return Collection
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getActivesByMstAdventBattleId(
        CarbonImmutable $now,
        string $mstAdventBattleId,
    ): Collection {
        $oprCampaigns = $this->getActiveCampaigns($now);
        return $oprCampaigns->filter(function (OprCampaignEntity $entity) use ($mstAdventBattleId) {
            // 対象のタイプが違うので除外
            if ($entity->getTargetType() !== CampaignTargetType::ADVENT_BATTLE->value) {
                return false;
            }

            // 対象IDが指定されている場合は照合する
            $targetId = $entity->getTargetId();
            if (StringUtil::isSpecified($targetId) && $targetId !== $mstAdventBattleId) {
                return false;
            }
            return true;
        });
    }
}
