<?php

namespace App\Filament\Pages;

use App\Constants\MissionStatus;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstMissionLimitedTerm;
use App\Models\Usr\UsrMissionLimitedTerm;
use App\Traits\ClockTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use App\Constants\MissionUnlockStatus;

class EditUsrMissionLimitedTerm extends UserDataBasePage
{
    use Authorizable;
    use ClockTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::MISSION_LIMITED_TERM->value;

    public string $mstMissionId = '';

    protected $queryString = [
        'userId',
        'mstMissionId',
    ];

    private ?MstMissionLimitedTerm $mstMissionLimitedTerm = null;
    private ?UsrMissionLimitedTerm $usrMissionLimitedTerm = null;

    public int $status;
    public int $progress;
    public int $isOpen;

    public function mount()
    {
        parent::mount();

        $usrMissionLimitedTerm = UsrMissionLimitedTerm::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_limited_term_id', $this->mstMissionId)
            ->first();
        $mstMissionLimitedTerm = MstMissionLimitedTerm::find($this->mstMissionId);

        $progress = 0;
        if ($mstMissionLimitedTerm !== null) {
            $progress = $usrMissionLimitedTerm?->progress ?? 0;
        }

        $this->status = $usrMissionLimitedTerm?->status ?? MissionStatus::UNCLEAR->value;
        $this->progress = $progress;
        $this->isOpen = $usrMission?->is_open ?? MissionUnlockStatus::LOCK->value;

        $this->form->fill([
            'status' => $this->status,
            'progress' => $this->progress,
            'isOpen' => $this->isOpen,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserMissionLimitedTerm::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstMissionId' => $this->mstMissionId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('progress')
                ->label('進捗値')
                ->numeric(),
            Select::make('status')
                ->label('ステータス')
                ->options(MissionStatus::labels()),
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
            $this->form->getState()['progress'],
            $this->form->getState()['isOpen'],
            $now,
        );

        $this->redirect(
            UserMissionLimitedTerm::getUrl(['userId' => $this->userId]),
        );
    }

    private function updateUsrMission(int $status, int $progress, int $isOpen, CarbonImmutable $now): void
    {
        $usrMissionLimitedTerm = UsrMissionLimitedTerm::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_mission_limited_term_id', $this->mstMissionId)
            ->first();

        if ($usrMissionLimitedTerm === null) {
            $usrMissionLimitedTerm = UsrMissionLimitedTerm::createAndInit(
                $this->userId,
                $this->mstMissionId,
            );
        }

        $usrMissionLimitedTerm->updateProgress($progress);
        $usrMissionLimitedTerm->updateIsOpen($isOpen);
        /** @var UsrMissionLimitedTerm $usrMissionLimitedTerm */
        switch ($status) {
            case MissionStatus::LOCKED->value:
                $usrMissionLimitedTerm->delete();
                return;
            case MissionStatus::UNCLEAR->value:
                $usrMissionLimitedTerm->unclear();
                break;
            case MissionStatus::CLEAR->value:
                $usrMissionLimitedTerm->clear($now);
                break;
            case MissionStatus::RECEIVED_REWARD->value:
                $usrMissionLimitedTerm->receiveReward($now);
                break;
        }

        $usrMissionLimitedTerm->save();
    }

    protected function getActions(): array
    {
        return [
            Action::make('update')
                ->label('更新')
                ->action(fn () => $this->update())
        ];
    }
}
