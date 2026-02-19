<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Models\Mst\MstComebackBonus;
use App\Models\Mst\MstComebackBonusSchedule;
use App\Models\Mst\MstDailyBonusReward;
use App\Traits\RewardInfoGetTrait;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class MstComebackBonusDetail extends Page
{
    use Authorizable;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static string $view = 'filament.pages.mst-comeback-bonus-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;
    protected static ?string $slug = 'mst-comeback-bonus-detail/{mstComebackBonusScheduleId}';

    public string $mstComebackBonusScheduleId;
    public ?MstComebackBonusSchedule $mstComebackBonusSchedule;
    public Collection $mstComebackBonusList;

    public function mount(): void
    {
        $this->mstComebackBonusScheduleId = request()->route('mstComebackBonusScheduleId');
        
        // スケジュール情報を取得
        $this->mstComebackBonusSchedule = MstComebackBonusSchedule::find($this->mstComebackBonusScheduleId);

        // 関連するカムバックボーナス情報を取得
        $this->mstComebackBonusList = MstComebackBonus::where('mst_comeback_bonus_schedule_id', $this->mstComebackBonusScheduleId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * カムバックボーナスの報酬情報を取得
     */
    public function getComebackBonusRewards(): array
    {
        $comebackBonusRewards = [];
        
        foreach ($this->mstComebackBonusList as $bonus) {
            // Daily Bonus Rewardを取得
            $mstDailyBonusRewards = MstDailyBonusReward::where('group_id', $bonus->mst_daily_bonus_reward_group_id)
                ->get();
            
            // 報酬DTOのリストを作成
            $rewardDtoList = $mstDailyBonusRewards->map(function (MstDailyBonusReward $reward) {
                return $reward->reward;
            });
            
            // 報酬情報を取得
            $rewardInfos = $this->getRewardInfos($rewardDtoList);
            
            $rewards = [];
            foreach ($mstDailyBonusRewards as $reward) {
                $rewards[] = $rewardInfos->get($reward->id);
            }
            
            $comebackBonusRewards[] = [
                'login_day_count' => $bonus->login_day_count,
                'sort_order' => $bonus->sort_order,
                'reward_group_id' => $bonus->mst_daily_bonus_reward_group_id,
                'rewards' => $rewards,
            ];
        }
        
        return $comebackBonusRewards;
    }

    public function getTitle(): string|Htmlable
    {
        return 'カムバックボーナス詳細: ' . $this->mstComebackBonusScheduleId;
    }

    protected function getViewData(): array
    {
        return [
            'mstComebackBonusSchedule' => $this->mstComebackBonusSchedule,
            'mstComebackBonusList' => $this->mstComebackBonusList,
            'comebackBonusRewards' => $this->getComebackBonusRewards(),
        ];
    }
}
