<?php

namespace App\Filament\Pages\Mission;

use App\Constants\MissionTabs;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

/**
 * ミッション情報画面の基底クラス
 */
abstract class MissionDataBasePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.mission-tab-info';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = '';

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
            ]
        ],
        [
            '期間限定' => [
                MissionTabs::MISSION_LIMITED_TERM->value => 'App\Filament\Pages\MstMissionLimitedTerms',
            ]
        ]
    ];

    /**
     * ヘッダーで表示されるタブ名
     * 各ページでこのプロパティを上書きする
     */
    public string $currentTab = '';

    public function getTabGroups(): array
    {
        return $this->tabGroups;
    }

    public function getCurrentTab(): string
    {
        return $this->currentTab;
    }

    public function getHeader(): ?View
    {
        return view('filament/common/mission-tab');
    }
}
