<?php

namespace App\Filament\Pages;

use App\Constants\MissionStatus;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstMissionEvent;
use App\Models\Mst\MstMissionEventDaily;
use App\Models\Usr\UsrMissionEvent;
use App\Traits\ClockTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use App\Constants\MissionType;
use App\Constants\MissionUnlockStatus;

class EditUserMissionEvent extends UserDataBasePage
{
    use Authorizable;
    use ClockTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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

    private ?MstMissionEvent $mstMissionEvent = null;
    private ?UsrMissionEvent $usrMissionEvent = null;

    public int $status;
    public int $progress;
    public int $unlockProgress;
    public int $isOpen;

    public function mount()
    {
        parent::mount();

        $usrMissionEvent = UsrMissionEvent::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_id', $this->mstMissionId)
            ->first();

        $mstMissionEvent = $this->getMstMission($this->missionType);

        $progress = 0;
        if ($mstMissionEvent !== null) {
            $progress = $usrMissionEvent?->progress ?? 0;
        }

        $this->status = $usrMissionEvent?->status ?? MissionStatus::UNCLEAR->value;
        $this->progress = $progress;
        $this->isOpen = $usrMission?->is_open ?? MissionUnlockStatus::LOCK->value;

        $this->form->fill([
            'status' => $this->status,
            'progress' => $this->progress,
            'isOpen' => $this->isOpen,
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
                ->label('ステータス')
                ->options(MissionStatus::labels()),
            TextInput::make('progress')
                ->label('進捗値')
                ->numeric()
                ->minValue(0),
            Select::make('isOpen')
                ->label('開放ステータス')
                ->options(MissionUnlockStatus::labels()),
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
            $this->form->getState()['progress'] ?? 0,
            $this->form->getState()['isOpen'],
            $now,
        );

        $this->redirect(
            $this->missionUrl,
        );
    }

    private function updateUsrMission(int $status, int $progress, int $isOpen, CarbonImmutable $now): void
    {
        $usrMissionEvent = UsrMissionEvent::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_id', $this->mstMissionId)
            ->where('mission_type', $this->missionType)
            ->first();

        if ($usrMissionEvent === null) {
            $usrMissionEvent = UsrMissionEvent::createAndInit(
                $this->userId,
                $this->mstMissionId,
                $this->missionType,
            );
        }

        $usrMissionEvent->updateProgress($progress);
        $usrMissionEvent->updateIsOpen($isOpen);
        /** @var UsrMissionEvent $usrMissionEvent */

        switch ($status) {
            case MissionStatus::LOCKED->value:
                $usrMissionEvent->delete();
                return;
            case MissionStatus::UNCLEAR->value:
                $usrMissionEvent->unclear();
                break;
            case MissionStatus::CLEAR->value:
                $usrMissionEvent->clear($now);
                break;
            case MissionStatus::RECEIVED_REWARD->value:
                $usrMissionEvent->receiveReward($now);
                break;
        }

        $usrMissionEvent->save();
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
            case MissionType::EVENT->value:
                $this->currentTab = UserSearchTabs::MISSION_EVENT->value;
                $this->missionUrl = UserMissionEvent::getUrl(['userId' => $this->userId]);
                break;
            case MissionType::EVENT_DAILY->value:
                $this->currentTab = UserSearchTabs::MISSION_EVENT_DAILY->value;
                $this->missionUrl = UserMissionEventDaily::getUrl(['userId' => $this->userId]);
                break;
        }
    }

    private function getMstMission($missionTypeNum) {
        $missionType = MissionType::getFromInt($missionTypeNum)->value;
        switch ($missionType) {
            case MissionType::EVENT->value:
                return MstMissionEvent::find($this->mstMissionId);
            case MissionType::EVENT_DAILY->value:
                return MstMissionEventDaily::find($this->mstMissionId);
        }
    }
}
