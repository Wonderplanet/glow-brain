<?php

namespace App\Filament\Pages;

use App\Constants\MissionStatus;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstMissionEventDailyBonus;
use App\Models\Usr\UsrMissionEventDailyBonus;
use App\Traits\ClockTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

class EditUserMissionEventDailyBonus extends UserDataBasePage
{
    use Authorizable;
    use ClockTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::MISSION_EVENT_DAILY_BONUS->value;

    public string $mstMissionId = '';

    protected $queryString = [
        'userId',
        'mstMissionId',
    ];

    public int $status;

    public function mount()
    {
        parent::mount();
        $usrMissionEventDailyBonus = UsrMissionEventDailyBonus::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_event_daily_bonus_id', $this->mstMissionId)
            ->first();
        $mstMissionEventDailyBonus = MstMissionEventDailyBonus::find($this->mstMissionId);

        $this->status = $usrMissionEventDailyBonus?->status ?? MissionStatus::UNCLEAR->value;

        $this->form->fill([
            'status' => $this->status,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserMissionEventDailyBonus::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstMissionId' => $this->mstMissionId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('status')
                ->label('ステータス')
                ->options(MissionStatus::labels()->toArray()),
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
            UserMissionEventDailyBonus::getUrl(['userId' => $this->userId]),
        );
    }

    private function updateUsrMission(int $status, CarbonImmutable $now): void
    {
        $usrMissionEventDailyBonus = UsrMissionEventDailyBonus::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_event_daily_bonus_id', $this->mstMissionId)
            ->first();

        if ($usrMissionEventDailyBonus?->getStatus() === $status) {
            return;
        }

        if ($usrMissionEventDailyBonus === null) {
            $usrMissionEventDailyBonus = UsrMissionEventDailyBonus::createAndInit(
                $this->userId,
                $this->mstMissionId,
                $now,
            );
        }
        /** @var UsrMissionEventDailyBonus $usrMissionEventDailyBonus */

        switch ($status) {
            case MissionStatus::LOCKED->value:
                $usrMissionEventDailyBonus->delete();
                return;
            case MissionStatus::UNCLEAR->value:
                $usrMissionEventDailyBonus->unclear($now);
                break;
            case MissionStatus::CLEAR->value:
                $usrMissionEventDailyBonus->clear($now);
                break;
            case MissionStatus::RECEIVED_REWARD->value:
                $usrMissionEventDailyBonus->receiveReward($now);
                break;
        }

        $usrMissionEventDailyBonus->save();
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
