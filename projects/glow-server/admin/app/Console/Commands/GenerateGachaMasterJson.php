<?php

namespace App\Console\Commands;

use App\Constants\GachaSetName;
use App\Domain\Common\Entities\Clock;
use App\Domain\Gacha\Entities\GachaBoxInterface;
use App\Domain\Item\Repositories\MstItemI18nRepository;
use App\Domain\Resource\Enums\RarityType;
use App\Domain\Resource\Enums\RewardType;
use App\Domain\Resource\Mst\Entities\OprGachaEntity;
use App\Domain\Resource\Mst\Entities\OprGachaUpperEntity;
use App\Domain\Resource\Mst\Repositories\MstItemRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitI18nRepository;
use App\Domain\Resource\Mst\Repositories\MstUnitRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaI18nRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaPrizeRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaRepository;
use App\Domain\Resource\Mst\Repositories\OprGachaUpperRepository;
use App\Operators\S3Operator;
use App\Services\ConfigGetService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateGachaMasterJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-gacha-master-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate json file for post-analysis of gacha.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->generateMstGachaJson();
    }

    private function generateMstGachaJson()
    {
        $s3DiskName = 's3_bne';

        /** @var ConfigGetService $configGetService */
        $configGetService = app()->make(ConfigGetService::class);
        $lang = $configGetService->getLocale();
        $appId = $configGetService->getAdminAppId();
        $appName = $configGetService->getAdminAppName();

        $pathPrefix = 'data/600';

        /** @var S3Operator $s3Operator */
        $s3Operator = app()->make(S3Operator::class);

        /** @var Clock $clock */
        $clock = app()->make(Clock::class);
        $targetDate = $clock->now()->subDay();

        // ガシャマスタJSONを生成する
        /** @var OprGachaRepository $oprGachaRepository */
        $oprGachaRepository = app()->make(OprGachaRepository::class);
        /** @var OprGachaI18nRepository $oprGachaI18nRepository */
        $oprGachaI18nRepository = app()->make(OprGachaI18nRepository::class);
        /** @var OprGachaUpperRepository $oprGachaUpperRepository */
        $oprGachaUpperRepository = app()->make(OprGachaUpperRepository::class);
        /** @var OprGachaPrizeRepository $oprGachaPrizeRepository */
        $oprGachaPrizeRepository = app()->make(OprGachaPrizeRepository::class);
        /** @var MstUnitRepository $mstUnitRepository */
        $mstUnitRepository = app()->make(MstUnitRepository::class);
        /** @var MstUnitI18nRepository $mstUnitI18nRepository */
        $mstUnitI18nRepository = app()->make(MstUnitI18nRepository::class);
        /** @var MstItemRepository $mstItemRepository */
        $mstItemRepository = app()->make(MstItemRepository::class);
        /** @var MstItemI18nRepository $mstItemI18nRepository */
        $mstItemI18nRepository = app()->make(MstItemI18nRepository::class);

        // 集計日中に開催中のガチャを絞り込む
        $targetStartDate = $targetDate->startOfDay();
        $targetEndDate = $targetDate->endOfDay();
        $oprGachas = $oprGachaRepository->getAll()->filter(function ($entity) use ($targetStartDate, $targetEndDate) {
            $startDate = new CarbonImmutable($entity->getStartAt());
            $endDate = new CarbonImmutable($entity->getEndAt());
            return $startDate->lte($targetEndDate) && $endDate->gte($targetStartDate);
        });

        $oprGachaIds = $oprGachas->map(fn($oprGacha) => $oprGacha->getId());
        $oprGachaI18ns = $oprGachaI18nRepository
            ->getByLanguage($lang)
            ->filter(function ($entity) use ($oprGachaIds) {
                return $oprGachaIds->contains($entity->getOprGachaId());
            })
            ->keyBy(fn($entity) => $entity->getOprGachaId());

        $upperGroups = $oprGachas->map(fn($oprGacha) => $oprGacha->getUpperGroup())->unique();
        $oprGachaUpperGroups = $oprGachaUpperRepository
            ->getAll()
            ->filter(function ($entity) use ($upperGroups) {
                return $upperGroups->contains($entity->getUpperGroup());
            })
            ->groupBy(fn($entity) => $entity->getUpperGroup());

        $prizeGroupIds = collect();
        foreach ($oprGachas as $oprGacha) {
            $prizeGroupIds->push($oprGacha->getPrizeGroupId());
            if ($oprGacha->hasFixedPrizeGroup()) {
                $prizeGroupIds->push($oprGacha->getFixedPrizeGroupId());
            }
        }

        $allOprGachaPrizes = $oprGachaPrizeRepository->getAll();
        $oprGachaPrizes = $allOprGachaPrizes
            ->filter(function ($entity) use ($prizeGroupIds) {
                return $prizeGroupIds->contains($entity->getGroupId());
            });
        $groupedOprGachaPrizes = $oprGachaPrizes->groupBy(fn($entity) => $entity->getGroupId());

        $mstUnitIds = collect();
        $mstItemIds = collect();
        foreach ($allOprGachaPrizes as $oprGachaPrize) {
            /** @var GachaBoxInterface $oprGachaPrize */
            if ($oprGachaPrize->isUnit()) {
                $mstUnitIds->add($oprGachaPrize->getResourceId());
            } elseif ($oprGachaPrize->isItem()) {
                $mstItemIds->add($oprGachaPrize->getResourceId());
            }
        }
        $mstUnits = $mstUnitRepository->getByIds($mstUnitIds->unique())->keyBy(fn($entity) => $entity->getId());
        $mstItems = $mstItemRepository->getByIds($mstItemIds->unique())->keyBy(fn($entity) => $entity->getId());
        $mstUnitI18ns = $mstUnitI18nRepository
            ->getByLanguage($lang)
            ->filter(function ($entity) use ($mstUnitIds) {
                return $mstUnitIds->contains($entity->getMstUnitId());
            })
            ->keyBy(fn($entity) => $entity->getMstUnitId());
        $mstItemI18ns = $mstItemI18nRepository
            ->getByLanguage($lang)
            ->filter(function ($entity) use ($mstItemIds) {
                return $mstItemIds->contains($entity->getMstItemId());
            })
            ->keyBy(fn($entity) => $entity->getMstItemId());

        $gachaMaster = [];
        foreach ($oprGachas as $oprGacha) {
            /** @var OprGachaEntity $oprGacha */
            $gachaTable = $groupedOprGachaPrizes->get($oprGacha->getPrizeGroupId(), collect());
            $totalWeight = $gachaTable->sum(fn($entity) => $entity->getWeight());

            // 通常枠
            $normalItems = $this->oprGachaPrizesToArray($gachaTable, $totalWeight);
            $sets = [
                [
                    'set_name' => GachaSetName::REGULAR->value,
                    'items' => $normalItems,
                ]
            ];

            // 10連確定枠
            if ($oprGacha->hasFixedPrizeGroup()) {
                $gachaTable = $groupedOprGachaPrizes->get($oprGacha->getFixedPrizeGroupId(), collect());
                $totalWeight = $gachaTable->sum(fn($entity) => $entity->getWeight());
                $fixedItems = $this->oprGachaPrizesToArray($gachaTable, $totalWeight);
                $sets[] = [
                    'set_name' => GachaSetName::FIXED->value,
                    'items' => $fixedItems,
                ];
            }

            // 天井がある場合は追加
            $oprGachaUppers = $oprGachaUpperGroups->get($oprGacha->getUpperGroup(), collect());
            if ($oprGachaUppers->isNotEmpty()) {
                foreach ($oprGachaUppers as $oprGachaUpper) {
                    /** @var OprGachaUpperEntity $oprGachaUpper */

                    $upperTable = collect();
                    foreach ($gachaTable as $gachaTableItem) {
                        /** @var GachaBoxInterface $gachaTableItem */
                        if ($gachaTableItem->isUnit()) {
                            $rarity = $mstUnits->get($gachaTableItem->getResourceId())?->getRarity();
                        } elseif ($gachaTableItem->isItem()) {
                            $rarity = $mstItems->get($gachaTableItem->getResourceId())?->getRarity();
                        } else {
                            continue;
                        }
                        if ($rarity === RarityType::UR->value) {
                            if ($oprGachaUpper->isMaxRarity() || ($oprGachaUpper->isPickup() && $gachaTableItem->getPickup())) {
                                $upperTable->add($gachaTableItem);
                            }
                        }
                    }
                    $maxRarityTableTotalWeight = $upperTable->sum(fn($entity) => $entity->getWeight());
                    $items = $this->oprGachaPrizesToArray($upperTable, $maxRarityTableTotalWeight);
                    $setName = $oprGachaUpper->isPickup() ? GachaSetName::PICKUP->value : GachaSetName::MAX_RARITY->value;
                    $sets[] = [
                        'set_name' => $setName,
                        'items' => $items
                    ];
                }
            }

            $gachaMaster[$oprGacha->getId()] = [
                'app_id' => $appId,
                'app_name' => $appName,
                'gasha_id' => $oprGacha->getId(),
                'gasha_name' => $oprGachaI18ns->get($oprGacha->getId())?->getName() ?? '',
                // BOXガチャや選択式ガチャが実装される場合は変更が必要
                'gasha_type' => 0,
                'gasha_group_id' => '',
                'open_time' => $oprGacha->getStartAt()->getTimestamp(),
                'close_time' => $oprGacha->getEndAt()->getTimestamp(),
                'steps' => [
                    ['sets' => $sets],
                ]
            ];
        }

        $formattedDate = $targetDate->format('Ymd');
        foreach ($gachaMaster as $oprGachaId => $master) {
            $gachaJson = json_encode($master);
            $s3Operator->put($s3DiskName, "$pathPrefix/$oprGachaId/gasha_$formattedDate.json", $gachaJson);
        }

        // アイテムマスタJSONを生成する
        $itemMaster = ['app_id' => $appId, 'items' => []];
        $addedItems = [];
        foreach ($allOprGachaPrizes as $oprGachaPrize) {
            /** @var GachaBoxInterface $oprGachaPrize */
            $targetId = $oprGachaPrize->getResourceId();
            if ($oprGachaPrize->isUnit()) {
                $name = $mstUnitI18ns->get($targetId)->getName();
                $rarity = $mstUnits->get($targetId)->getRarity();
            } elseif ($oprGachaPrize->isItem()) {
                $name = $mstItemI18ns->get($targetId)->getName();
                $rarity = $mstItems->get($targetId)->getRarity();
            } else {
                continue;
            }

            $duplicateCheckKey = $oprGachaPrize->getResourceType()->value . ":" . $targetId;
            if (isset($addedItems[$duplicateCheckKey])) {
                // 重複している場合はスキップ
                continue;
            }
            $addedItems[$duplicateCheckKey] = true;
            $itemMaster['items'][] = [
                'id' => $targetId,
                'name' => $name,
                'rarity' => $rarity,
            ];
        }

        $itemJson = json_encode($itemMaster);
        $s3Operator->put($s3DiskName, "$pathPrefix/items_$formattedDate.json", $itemJson);
    }

    /**
     * json出力用の配列に整形する
     * @param Collection $oprGachaPrizes
     * @param int        $totalWeight
     * @return array
     */
    private function oprGachaPrizesToArray(Collection $oprGachaPrizes, int $totalWeight): array
    {
        $items = [];
        foreach ($oprGachaPrizes as $oprGachaPrize) {
            /** @var GachaBoxInterface $oprGachaPrize */

            $resourceId = $oprGachaPrize->getResourceId();
            if ($oprGachaPrize->getResourceType() === RewardType::COIN) {
                // id未設定だとエラーになるのでresource_idが存在しないCoinはcoinを設定
                $resourceId = 'coin';
            }
            $items[] = [
                'id' => $resourceId,
                'item_num' => $oprGachaPrize->getResourceAmount(),
                // キー名はweightだが、実際はパーセンテージ
                'weight' => $this->calcPercentage($oprGachaPrize->getWeight(), $totalWeight),
                'weight_sp' => 0,
            ];
        }
        return $items;
    }

    /**
     * weightをパーセンテージに変換する
     * @param int $weight
     * @param int $totalWeight
     * @return float
     */
    private function calcPercentage(int $weight, int $totalWeight): float
    {
        if ($totalWeight === 0) {
            // 0除算回避
            return 0;
        }
        return $weight / $totalWeight * 100;
    }
}
