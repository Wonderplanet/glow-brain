<?php

namespace App\Filament\Pages\Mission;

use App\Constants\MissionTabs;
use App\Constants\MstDataMenuDisplayOrder;
use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use Filament\Pages\Page;
use Livewire\WithPagination;

class MissionTabInfo extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::MASTER_DATA_VIEWER->value;

    protected static string $view = 'filament.pages.mission-tab-info';

    protected static ?string $title = 'ミッション・ログボ';

    public string $currentTab = '';
    protected static ?int $navigationSort = MstDataMenuDisplayOrder::MISSION_DISPLAY_ORDER->value; // メニューの並び順

    /**
     * タブ名と遷移先のURL
     */
    public array $tabGroups = [
        [
            '通常' => [
                MissionTabs::MISSION_ACHIEVEMENT->value => 'App\Filament\Pages\MstMissionAchievements',
                MissionTabs::MISSION_BEGINNER->value => 'App\Filament\Pages\MstMissionBeginners',
                MissionTabs::MISSION_DAILY->value => 'App\Filament\Pages\MstMissionDailies',
                MissionTabs::MISSION_WEEKLY->value => 'App\Filament\Pages\MstMissionWeeklies',
                MissionTabs::MISSION_DAILY_BONUS->value => 'App\Filament\Pages\MstMissionDailyBonuses',
            ],
        ],
        [
            'イベント' => [
                MissionTabs::MISSION_EVENT->value => 'App\Filament\Pages\MstMissionEvents',
                MissionTabs::MISSION_EVENT_DAILY->value => 'App\Filament\Pages\MstMissionEventDailies',
                MissionTabs::MISSION_EVENT_DAILY_BONUS->value => 'App\Filament\Pages\MstMissionEventDailyBonuses',
            ],
        ],
        [
            '期間限定' => [
                MissionTabs::MISSION_LIMITED_TERM->value => 'App\Filament\Pages\MstMissionLimitedTerms',
            ],
        ]
    ];

    public function getTabGroups(): array
    {
        return $this->tabGroups;
    }
}
