<?php

namespace App\Filament\Pages;

use App\Constants\MissionStatus;
use App\Constants\MissionType;
use App\Constants\MissionUnlockStatus;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstMissionAchievement;
use App\Models\Mst\MstMissionBeginner;
use App\Models\Mst\MstMissionDaily;
use App\Models\Mst\MstMissionWeekly;
use App\Models\Usr\UsrMissionNormal;
use App\Traits\ClockTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserMission extends UserDataBasePage
{
    use Authorizable;
    use ClockTrait;

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = '';
    public string $missionUrl = '';

    public string $mstMissionId = '';
    public string $missionType = '';

    protected $queryString = [
        'userId',
        'mstMissionId',
        'missionType',
    ];

    public int $status;
    public int $isOpen;
    public int $progress;
    public int $unlockProgress;

    public function mount()
    {
        parent::mount();

        $usrMission = UsrMissionNormal::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_id', $this->mstMissionId)
            ->where('mission_type', $this->missionType)
            ->first();

        $mstMission = $this->getMstMission($this->missionType);

        $progress = 0;
        if ($mstMission !== null) {
            $progress = $usrMission?->progress ?? 0;
        }

        $this->status = $usrMission?->status ?? MissionStatus::UNCLEAR->value;
        $this->isOpen = $usrMission?->is_open ?? MissionUnlockStatus::LOCK->value;
        $this->progress = $progress;
        $this->unlockProgress = $usrMission?->unlock_progress ?? 0;

        $this->form->fill([
            'status' => $this->status,
            'isOpen' => $this->isOpen,
            'progress' => $this->progress,
            'unlockProgress' => $this->unlockProgress,
        ]);

        $this->getMissionCurrentLocation($this->missionType);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            $this->missionUrl => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstMissionId' => $this->mstMissionId, 'missionType' => $this->missionType]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('status')
                ->label('ミッションステータス')
                ->options(MissionStatus::labels()),
            Select::make('isOpen')
                ->label('開放ステータス')
                ->options(MissionUnlockStatus::labels()),
            TextInput::make('progress')
                ->label('進捗値')
                ->numeric()
                ->minValue(0),
            TextInput::make('unlockProgress')
                ->label('開放進捗値')
                ->numeric()
                ->minValue(0),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        $now = $this->now();

        $this->updateUsrMission(
            $this->form->getState()['status'],
            $this->form->getState()['isOpen'],
            $this->form->getState()['progress'] ?? 0,
            $this->form->getState()['unlockProgress'] ?? 0,
            $now,
        );

        $this->redirect(
            $this->missionUrl,
        );
    }

    private function updateUsrMission(int $status, int $isOpen, int $progress, int $unlockProgress, CarbonImmutable $now): void
    {
        $usrMission = UsrMissionNormal::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_id', $this->mstMissionId)
            ->where('mission_type', $this->missionType)
            ->first();

        if ($usrMission === null) {
            $usrMission = UsrMissionNormal::createAndInit(
                $this->userId,
                $this->mstMissionId,
                $this->missionType,
                $now,
            );
        }
        $usrMission->updateProgress($progress);
        $usrMission->updateUnlockProgress($unlockProgress);
        $usrMission->updateIsOpen($isOpen);
        /** @var UsrMissionDaily $usrMission */

        switch ($status) {
            case MissionStatus::LOCKED->value:
                $usrMission->delete();
                return;
            case MissionStatus::UNCLEAR->value:
                $usrMission->unclear($now);
                break;
            case MissionStatus::CLEAR->value:
                $usrMission->clear($now);
                break;
            case MissionStatus::RECEIVED_REWARD->value:
                $usrMission->receiveReward($now);
                break;
        }

        $usrMission->save();
    }

    protected function getActions(): array
    {
        return [
            Action::make('update')
                ->label('更新')
                ->action(fn () => $this->update())
        ];
    }

    private function getMissionCurrentLocation($missionTypeNum) {
        $missionType = MissionType::getFromInt($missionTypeNum)->value;
        switch ($missionType) {
            case MissionType::ACHIEVEMENT->value:
                $this->currentTab = UserSearchTabs::MISSION_ACHIEVEMENT->value;
                $this->missionUrl = UserMissionAchievement::getUrl(['userId' => $this->userId]);
                break;
            case MissionType::DAILY->value:
                $this->currentTab = UserSearchTabs::MISSION_DAILY->value;
                $this->missionUrl = UserMissionDaily::getUrl(['userId' => $this->userId]);
                break;
            case MissionType::WEEKLY->value:
                $this->currentTab = UserSearchTabs::MISSION_WEEKLY->value;
                $this->missionUrl = UserMissionWeekly::getUrl(['userId' => $this->userId]);
                break;
            case MissionType::BEGINNER->value:
                $this->currentTab = UserSearchTabs::MISSION_BEGINNER->value;
                $this->missionUrl = UserMissionBeginner::getUrl(['userId' => $this->userId]);
                break;
        }
    }

    private function getMstMission($missionTypeNum) {
        $missionType = MissionType::getFromInt($missionTypeNum)->value;
        switch ($missionType) {
            case MissionType::ACHIEVEMENT->value:
                return MstMissionAchievement::find($this->mstMissionId);
            case MissionType::DAILY->value:
                return MstMissionDaily::find($this->mstMissionId);
            case MissionType::WEEKLY->value:
                return MstMissionWeekly::find($this->mstMissionId);
            case MissionType::BEGINNER->value:
                return MstMissionBeginner::find($this->mstMissionId);
        }
    }
}
