<?php

namespace App\Services\LogTrigger;

use App\Constants\LogResourceTriggerSource;
use App\Dtos\LogTriggerDto;
use App\Filament\Pages\MstShopItems;
use App\Models\Mst\MstMissionAchievement;
use App\Models\Mst\MstMissionBeginner;
use App\Models\Mst\MstMissionDaily;
use App\Models\Mst\MstMissionWeekly;
use App\Models\Mst\MstMissionEvent;
use App\Models\Mst\MstMissionEventDaily;
use App\Models\Mst\MstMissionEventDailyBonus;
use App\Models\Mst\MstMissionReward;
use App\Models\Mst\MstMissionLimitedTerm;
use App\Models\Mst\MstShopPass;
use App\Models\Mst\MstShopItem;
use App\Models\Mst\MstItem;
use App\Models\Mst\MstStage;
use App\Models\Mst\OprGachaPrize;
use Illuminate\Support\Collection;
use App\Entities\LogTrigger;
use App\Constants\RewardType;
use App\Constants\IdleIncentiveExecMethod;
use App\Filament\Pages\StageDetail;
use App\Filament\Pages\MstItemDetail;
use App\Filament\Pages\MstShopItemDetail;
use App\Filament\Pages\MstShopPassDetail;
use App\Filament\Pages\MstMissionLimitedTermsDetail;
use App\Filament\Pages\MstMissionEventDailyDetail;
use App\Filament\Pages\MstMissionEventsDetail;
use App\Filament\Pages\MstMissionWeeklyDetail;
use App\Filament\Pages\MstMissionDailyDetail;
use App\Filament\Pages\MstMissionBeginnerDetail;
use App\Filament\Pages\MstMissionAchievementDetail;

class LogTriggerInfoGetService
{
    /**
     * @param Collection<LogTriggerDto> $logTriggerDtos
     * @return Collection<string, LogTrigger> key: trigger_value
     */
    public function createLogTriggers(Collection $logTriggerDtos): Collection
    {
        $groupedLogTriggerDtos = $logTriggerDtos->groupBy(function (LogTriggerDto $logTriggerDto) {
            return $logTriggerDto->getTriggerSource();
        });

        $logTriggers = collect();
        $triggerArray = json_decode($groupedLogTriggerDtos, true);

        foreach (array_keys($triggerArray) as $key) {
            switch ($key) {
                case LogResourceTriggerSource::MISSION_ACHIEVEMENT_REWARD->value :
                    $mstMissionAchievementLogTriggers = $this->getMstMissionAchievementLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_ACHIEVEMENT_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionAchievementLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_BEGINNER_REWARD->value :
                    $mstMissionBeginnerLogTriggers = $this->getMissionBeginnerLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_BEGINNER_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionBeginnerLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_DAILY_REWARD->value :
                    $mstMissionDailyLogTriggers = $this->getMstMissionDailyLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_DAILY_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionDailyLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_WEEKLY_REWARD->value :
                    $mstMissionWeeklyLogTriggers = $this->getMstMissionWeeklyLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_WEEKLY_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionWeeklyLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_EVENT_REWARD->value :
                    $mstMissionEventLogTriggers = $this->getMstMissionEventLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_EVENT_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionEventLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_EVENT_DAILY_REWARD->value :
                    $mstMissionEventDailyLogTriggers = $this->getMstMissionEventDailyLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_EVENT_DAILY_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionEventDailyLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_LIMITED_TERM_REWARD->value :
                    $mstMissionLimitedTermLogTriggers = $this->getMstMissionLimitedTermLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_LIMITED_TERM_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionLimitedTermLogTriggers);
                    break;
                case LogResourceTriggerSource::SHOP_PACK_CONTENT_REWARD->value :
                    $mstShopPassLogTriggers = $this->getMstShopPassLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::SHOP_PACK_CONTENT_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstShopPassLogTriggers);
                    break;
                case LogResourceTriggerSource::SHOP_ITEM_REWARD->value :
                    $mstShopItemLogTriggers = $this->getMstShopItemLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::SHOP_ITEM_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstShopItemLogTriggers);
                    break;
                case LogResourceTriggerSource::TRADE_SHOP_ITEM_COST->value :
                    $tradeShopItemCostLogTriggers = $this->getMstShopItemLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::TRADE_SHOP_ITEM_COST->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($tradeShopItemCostLogTriggers);
                    break;
                case LogResourceTriggerSource::MISSION_EVENT_DAILY_BONUS_REWARD->value :
                    $mstMissionEventDailyBonusLogTriggers = $this->getMstMissionEventDailyBonusLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::MISSION_EVENT_DAILY_BONUS_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($mstMissionEventDailyBonusLogTriggers);
                    break;
                case LogResourceTriggerSource::GACHA_REWARD->value :
                    $oprGachaLogTriggers = $this->getOprGachaLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::GACHA_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($oprGachaLogTriggers);
                    break;
                case LogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value :
                    $stageAlwaysClearRewardsLogTriggers = $this->getStageAlwaysClearRewardTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($stageAlwaysClearRewardsLogTriggers);
                    break;
                case LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value :
                    $stageAlwaysClearRewardsLogTriggers = $this->getStageAlwaysClearRewardTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::STAGE_FIRST_CLEAR_REWARD->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($stageAlwaysClearRewardsLogTriggers);
                    break;
                case LogResourceTriggerSource::STAGE_CHALLENGE_COST->value :
                    $stageAlwaysClearRewardsLogTriggers = $this->getStageAlwaysClearRewardTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::STAGE_CHALLENGE_COST->value, collect()));
                    $logTriggers = $logTriggers
                        ->union($stageAlwaysClearRewardsLogTriggers);
                    break;
                case LogResourceTriggerSource::IDLE_INCENTIVE_REWARD->value :
                    $idleIncentiveRewardLogTriggers = $this->getIdleIncentiveRewardLogTriggers($groupedLogTriggerDtos->get(LogResourceTriggerSource::IDLE_INCENTIVE_REWARD->value, collect()));

                    $logTriggers = $logTriggers
                        ->union($idleIncentiveRewardLogTriggers);
                    break;
            }
        }

        return $logTriggers;
    }

    private function getIdleIncentiveRewardLogTriggers(Collection $logTriggerDtos): Collection
    {
        $logTriggers = collect();
        foreach ($logTriggerDtos as $logTriggerDto) {
            $incentiveExecMethod = IdleIncentiveExecMethod::tryFrom($logTriggerDto->getTriggerValue());
            $logTriggers->put($logTriggerDto->getTriggerValue(), new LogTrigger(
                '',
                '',
                '',
                $incentiveExecMethod->label(),
                ''
            ));
        }

        return $logTriggers;
    }

    private function getMstMissionAchievementLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionAchievementIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionAchievements = MstMissionAchievement::query()
            ->whereIn('id', $mstMissionAchievementIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionAchievement $mstMissionAchievement) {
                return [LogResourceTriggerSource::MISSION_ACHIEVEMENT_REWARD->value . $mstMissionAchievement->id => [
                    'name' => '[' . $mstMissionAchievement->id . '] ' . $mstMissionAchievement->mst_mission_i18n->description,
                    'link' => MstMissionAchievementDetail::getUrl(['mstMissionAchievementId' => $mstMissionAchievement->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionAchievements);
    }

    private function getMissionBeginnerLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionBeginnerIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionBeginners = MstMissionBeginner::query()
            ->whereIn('id', $mstMissionBeginnerIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionBeginner $mstMissionBeginner) {
                return [LogResourceTriggerSource::MISSION_BEGINNER_REWARD->value . $mstMissionBeginner->id => [
                    'name' => '[' . $mstMissionBeginner->id . '] ' . $mstMissionBeginner->mst_mission_i18n->description,
                    'link' => MstMissionBeginnerDetail::getUrl(['mstMissionBeginnerId' => $mstMissionBeginner->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionBeginners);
    }

    private function getMstMissionDailyLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionDailyIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionDailys = MstMissionDaily::query()
            ->whereIn('id', $mstMissionDailyIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionDaily $mstMissionDaily) {
                return [LogResourceTriggerSource::MISSION_DAILY_REWARD->value . $mstMissionDaily->id => [
                    'name' => '[' . $mstMissionDaily->id . '] ' . $mstMissionDaily->mst_mission_i18n->description,
                    'link' => MstMissionDailyDetail::getUrl(['mstMissionDailyId' => $mstMissionDaily->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionDailys);
    }

    private function getMstMissionWeeklyLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionWeeklyIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionWeeklys = MstMissionWeekly::query()
            ->whereIn('id', $mstMissionWeeklyIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionWeekly $mstMissionWeekly) {
                return [LogResourceTriggerSource::MISSION_WEEKLY_REWARD->value . $mstMissionWeekly->id => [
                    'name' => '[' . $mstMissionWeekly->id . '] ' . $mstMissionWeekly->mst_mission_i18n->description,
                    'link' => MstMissionWeeklyDetail::getUrl(['mstMissionWeeklyId' => $mstMissionWeekly->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionWeeklys);
    }

    private function getMstMissionEventLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionEventIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionEvents = MstMissionEvent::query()
            ->whereIn('id', $mstMissionEventIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionEvent $mstMissionEvent) {
                return [LogResourceTriggerSource::MISSION_EVENT_REWARD->value . $mstMissionEvent->id => [
                    'name' => '[' . $mstMissionEvent->id . '] ' . $mstMissionEvent->mst_mission_i18n->description,
                    'link' => MstMissionEventsDetail::getUrl(['mstMissionEventId' => $mstMissionEvent->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionEvents);
    }

    private function getMstMissionEventDailyLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionEventDailyIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionEventDailys = MstMissionEventDaily::query()
            ->whereIn('id', $mstMissionEventDailyIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionEventDaily $mstMissionEventDaily) {
                return [LogResourceTriggerSource::MISSION_EVENT_DAILY_REWARD->value . $mstMissionEventDaily->id => [
                    'name' => '[' . $mstMissionEventDaily->id . '] ' . $mstMissionEventDaily->mst_mission_i18n->description,
                    'link' => MstMissionEventDailyDetail::getUrl(['mstMissionEventDailyId' => $mstMissionEventDaily->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionEventDailys);
    }

    private function getMstMissionLimitedTermLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionLimitedTermIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionLimitedTerms = MstMissionLimitedTerm::query()
            ->whereIn('id', $mstMissionLimitedTermIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionLimitedTerm $mstMissionLimitedTerm) {
                return [LogResourceTriggerSource::MISSION_LIMITED_TERM_REWARD->value . $mstMissionLimitedTerm->id => [
                    'name' => '[' . $mstMissionLimitedTerm->id . '] ' . $mstMissionLimitedTerm->mst_mission_i18n->description,
                    'link' => MstMissionLimitedTermsDetail::getUrl(['mstMissionLimitedTermId' => $mstMissionLimitedTerm->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstMissionLimitedTerms);
    }

    private function getMstShopPassLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstShopPassIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstShopPasss = MstShopPass::query()
            ->whereIn('id', $mstShopPassIds->keys())
            ->get()
            ->mapWithKeys(function (MstShopPass $mstShopPass) {
                return [LogResourceTriggerSource::SHOP_PACK_CONTENT_REWARD->value . $mstShopPass->id => [
                    'name' => '[' . $mstShopPass->id . '] ' . $mstShopPass->mst_shop_pass_i18n->name,
                    'link' => MstShopPassDetail::getUrl(['mstShopPassId' => $mstShopPass->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstShopPasss);
    }


    private function getMstShopItemLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstShopItems = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => $logTriggerDto->getTriggerSource()];
        });

        $triggerSource = $logTriggerDtos->map(function (LogTriggerDto $logTriggerDto) {
            return $logTriggerDto->getTriggerSource();
        })->first();

        $mstShopItems = MstShopItem::query()
            ->whereIn('id', $mstShopItems->keys())
            ->get()
            ->mapWithKeys(function (MstShopItem $mstShopItem) use ($triggerSource) {
                return [$triggerSource . $mstShopItem->id => [
                    'resource_type' => $mstShopItem->resource_type,
                    'resource_id' => $mstShopItem->resource_id,
                    'link' =>  MstShopItems::getUrl(),
                ]];
            });

        return $this->makeLogRewardTriggers($logTriggerDtos, $mstShopItems);
    }

    private function getMstMissionEventDailyBonusLogTriggers(Collection $logTriggerDtos): Collection
    {
        $mstMissionEventDailyBonusIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstMissionEventDailyBonusIds = MstMissionEventDailyBonus::query()
            ->whereIn('id', $mstMissionEventDailyBonusIds->keys())
            ->get()
            ->pluck('id','mst_mission_reward_group_id');

        $mstMissionRewards = MstMissionReward::query()
            ->whereIn('group_id', $mstMissionEventDailyBonusIds->keys())
            ->get()
            ->mapWithKeys(function (MstMissionReward $mstMissionReward) use ($mstMissionEventDailyBonusIds) {
                $id = $mstMissionEventDailyBonusIds->get($mstMissionReward->group_id);
                return [LogResourceTriggerSource::MISSION_EVENT_DAILY_BONUS_REWARD->value . $id => [
                    'resource_type' => $mstMissionReward->resource_type,
                    'resource_id' => $mstMissionReward->resource_id,
                    'link' => MstItemDetail::getUrl(['mstItemId' => $id]),
                ]];
            });

        return $this->makeLogRewardTriggers($logTriggerDtos, $mstMissionRewards);
    }

    private function getStageAlwaysClearRewardTriggers(Collection $logTriggerDtos): Collection
    {
        $mstStageIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $mstStages = MstStage::query()
            ->whereIn('id', $mstStageIds->keys())
            ->get()
            ->mapWithKeys(function (MstStage $mstStage) {
                return [LogResourceTriggerSource::STAGE_ALWAYS_CLEAR_REWARD->value . $mstStage->id => [
                    'name' => 'ステージ:[' . $mstStage?->id . '] ' . $mstStage?->mst_stage_i18n?->name,
                    'link' => StageDetail::getUrl(['stageId' => $mstStage?->id]),
                ]];
            });

        return $this->makeLogTriggers($logTriggerDtos, $mstStages);
    }

    private function getOprGachaLogTriggers(Collection $logTriggerDtos): Collection
    {
        $oprGachaPrizeIds = $logTriggerDtos->mapWithKeys(function (LogTriggerDto $logTriggerDto) {
            return [$logTriggerDto->getTriggerValue() => true];
        });

        $oprGachaPrizes = OprGachaPrize::query()
            ->whereIn('id', $oprGachaPrizeIds->keys())
            ->get()
            ->mapWithKeys(function (OprGachaPrize $oprGachaPrize) {
                return [LogResourceTriggerSource::GACHA_REWARD->value . $oprGachaPrize->id => [
                    'resource_type' => $oprGachaPrize->resource_type->value,
                    'resource_id' => $oprGachaPrize->resource_id,
                    'link' => MstItemDetail::getUrl(['mstItemId' => $oprGachaPrize->resource_id]),
                ]];
            });

        return $this->makeLogRewardTriggers($logTriggerDtos, $oprGachaPrizes);
    }

    /**
     * Summary of makeLogTriggers
     * @param mixed $logTriggerDtos
     * @param mixed $mstDatas
     * @return Collection<string, LogTrigger> key: trigger_value
     */
    private function makeLogTriggers($logTriggerDtos, $mstDatas) {

        $logTriggers = collect();
        foreach ($logTriggerDtos as $logTriggerDto) {
            $mstData = $mstDatas->get($logTriggerDto->getLogTriggerKeyAttribute());
            if ($mstData === null) {
                continue;
            }
            // TODO: trigger_valueだけだと、trigger_sourceが同じ場合に上書きしてしまうので要修正
            $key = $logTriggerDto->getTriggerValue();
            $logTriggers->put($key, new LogTrigger(
                $logTriggerDto->getTriggerSource(),
                $logTriggerDto->getTriggerValue(),
                $logTriggerDto->getTriggerOption(),
                $mstData['name'] ?? '',
                $mstData['link'] ?? ''
            ));
        }
        return $logTriggers;
    }

    private function makeLogRewardTriggers($logTriggerDtos, $mstDatas) {

        $logTriggers = collect();
        $items = MstItem::query()
            ->with([
                'mst_item_i18n'
            ])
            ->get()
            ->keyBy('id');

        foreach ($logTriggerDtos as $logTriggerDto) {
            $mstData = $mstDatas->get($logTriggerDto->getLogTriggerKeyAttribute());

            if ($mstData === null) {
                continue;
            }

            $key = $logTriggerDto->getTriggerValue();
            switch ($mstData['resource_type']) {
                case RewardType::ITEM->value :
                    $name = '[' . $items[$mstData['resource_id']]->id . '] ' .$items[$mstData['resource_id']]->mst_item_i18n->name;
                    break;
                case RewardType::COIN->value :
                    $name = RewardType::COIN->label();
                    break;
                case RewardType::FREE_DIAMOND->value :
                    $name = RewardType::FREE_DIAMOND->label();
                    break;
                case RewardType::AD->value :
                    $name = RewardType::AD->label();
                    break;
            }

            $logTriggers->put($key, new LogTrigger(
                $logTriggerDto->getTriggerSource(),
                $logTriggerDto->getTriggerValue(),
                $logTriggerDto->getTriggerOption(),
                $name ?? '',
                $mstData['link'] ?? ''
            ));
        }
        return $logTriggers;
    }

}
