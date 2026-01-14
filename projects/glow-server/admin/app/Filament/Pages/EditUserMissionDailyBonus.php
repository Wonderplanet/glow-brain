<?php

namespace App\Filament\Pages;

use App\Constants\MissionStatus;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstMissionDailyBonus;
use App\Models\Usr\UsrMissionDailyBonus;
use App\Traits\ClockTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

class EditUserMissionDailyBonus extends UserDataBasePage
{
    use Authorizable;
    use ClockTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::MISSION_WEEKLY->value;

    public string $mstMissionId = '';

    protected $queryString = [
        'userId',
        'mstMissionId',
    ];

    public int $status;

    public function mount()
    {
        parent::mount();
        $usrMission = UsrMissionDailyBonus::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_daily_bonus_id', $this->mstMissionId)
            ->first();
        $mstMission = MstMissionDailyBonus::find($this->mstMissionId);

        $this->status = $usrMission?->status ?? MissionStatus::UNCLEAR->value;

        $this->form->fill([
            'status' => $this->status,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserMissionDailyBonus::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstMissionId' => $this->mstMissionId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('status')
                ->label('ステータス')
                ->options(MissionStatus::labels()),
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
            $now,
        );

        $this->redirect(
            UserMissionDailyBonus::getUrl(['userId' => $this->userId]),
        );
    }

    private function updateUsrMission(int $status, CarbonImmutable $now): void
    {
        $usrMission = UsrMissionDailyBonus::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_daily_bonus_id', $this->mstMissionId)
            ->first();

        if ($usrMission?->getStatus() === $status) {
            return;
        }

        if ($usrMission === null) {
            $usrMission = UsrMissionDailyBonus::createAndInit(
                $this->userId,
                $this->mstMissionId,
                $now,
            );
        }
        /** @var UsrMissionDailyBonus $usrMission */

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
                ->requiresConfirmation()
                ->action(fn () => $this->update())
        ];
    }
}
