<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrParty;
use App\Models\Usr\UsrUnit;
use App\Traits\NotificationTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserParty extends UserDataBasePage
{
    use Authorizable;
    use NotificationTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.common.update-form-page';
    public string $currentTab = UserSearchTabs::PARTY->value;

    // ページを特定できる一意の情報
    public int $partyNo = 1;

    protected $queryString = [
        'userId',
        'partyNo',
    ];

    // formパラメータ
    public string $party_name = '';
    public string $usr_unit_id_1 = '';
    public ?string $usr_unit_id_2 = null;
    public ?string $usr_unit_id_3 = null;
    public ?string $usr_unit_id_4 = null;
    public ?string $usr_unit_id_5 = null;
    public ?string $usr_unit_id_6 = null;
    public ?string $usr_unit_id_7 = null;
    public ?string $usr_unit_id_8 = null;
    public ?string $usr_unit_id_9 = null;
    public ?string $usr_unit_id_10 = null;

    private bool $hasValidationError = false;

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserParty::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'partyNo' => $this->partyNo]) => '編集',
        ]);

        $this->setFormValues();
    }

    private function setFormValues(): void
    {
        $usrParty = $this->getUsrParty();

        if ($usrParty === null) {
            $this->redirect(
                UserParty::getUrl(['userId' => $this->userId]),
            );
            $this->sendDangerNotification(
                title: 'データが見つかりませんでした',
                body: 'パーティID: ' . $this->partyNo,
            );
            return;
        }

        $this->party_name = $usrParty->getPartyName();
        $usrUnitIds = $usrParty->getUsrUnitIds();
        foreach ($usrUnitIds as $index => $usrUnitId) {
            $this->{'usr_unit_id_' . $index + 1} = $usrUnitId;
        }
    }

    private function getUsrParty(): ?UsrParty
    {
        return UsrParty::query()
            ->where('usr_user_id', $this->userId)
            ->where('party_no', $this->partyNo)
            ->first();
    }

    private function validateUniqueUnit(): void
    {
        // 同じキャラをパーティに編成していないか検証
        $this->hasValidationError = false;
        $formData = array_filter($this->form->getState(), fn ($param) =>  !is_null($param));
        $usrUnitMap = [];
        foreach ($formData as $formName => $usrUnitId) {
            $usrUnitMap[$usrUnitId][] = $formName;
        }

        foreach ($usrUnitMap as $formNames) {
            if (count($formNames) > 1) {
                foreach ($formNames as $formName) {
                    $this->hasValidationError = true;
                    $this->addError($formName, '同じキャラをパーティに編成することはできません。');
                }
            }
        }
    }

    public function form(Form $form): Form
    {
        // 所持しているキャラの配列化
        $selectOptions = UsrUnit::query()
            ->with('mst_unit.mst_unit_i18n')
            ->where('usr_user_id', $this->userId)
            ->get()
            ->mapWithKeys(function (UsrUnit $usrUnit) {
                $mstUnit = $usrUnit->mst_unit;
                $unitName = $mstUnit->mst_unit_i18n->name ?? '';
                return [
                    $usrUnit->id => "[$mstUnit->id] $unitName",
                ];
            })
            ->toArray();

        $selectFields = [];
        foreach (range(1, 10) as $index) {
            $selectFields[] = Select::make("usr_unit_id_$index")
                ->label("キャラ$index")
                ->options($selectOptions)
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    $this->validateUniqueUnit();
                });
        }

        return $form
            ->schema([
                TextInput::make('party_name')->label('パーティ名')->required(),
                ...$selectFields,
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('update')
                ->label('更新')
                ->requiresConfirmation()
                ->disabled(fn () => $this->hasValidationError)
                ->action(fn () => $this->update()),
        ];
    }

    public function update()
    {
        $state = $this->form->getState();

        $this->updateUsrParty($state);

        $this->redirect(
            UserParty::getUrl(['userId' => $this->userId]),
        );
    }

    private function updateUsrParty(array $state): void
    {
        $usrUnitIds = [];
        foreach (range(1, 10) as $index) {
            $usrUnitIds[] = $state['usr_unit_id_' . $index];
        }
        $usrUnitIds = array_values(array_filter($usrUnitIds));

        // キャラ指定が10体未満の場合は前に詰めて不足分をnull(未編成)で埋める
        if (count($usrUnitIds) < 10) {
            $usrUnitIds = array_pad($usrUnitIds, 10, null);
        }

        $usrParty = $this->getUsrParty();
        $usrParty->party_name = $state['party_name'];
        $usrParty->usr_unit_id_1 = $usrUnitIds[0];
        $usrParty->usr_unit_id_2 = $usrUnitIds[1];
        $usrParty->usr_unit_id_3 = $usrUnitIds[2];
        $usrParty->usr_unit_id_4 = $usrUnitIds[3];
        $usrParty->usr_unit_id_5 = $usrUnitIds[4];
        $usrParty->usr_unit_id_6 = $usrUnitIds[5];
        $usrParty->usr_unit_id_7 = $usrUnitIds[6];
        $usrParty->usr_unit_id_8 = $usrUnitIds[7];
        $usrParty->usr_unit_id_9 = $usrUnitIds[8];
        $usrParty->usr_unit_id_10 = $usrUnitIds[8];
        $usrParty->save();
    }
}
