<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrUserParameter;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserParameter extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::USER_PARAMETER->value;

    public int $level;
    public int $exp;
    public int $coin;
    public int $stamina;
    public ?string $staminaUpdatedAt;


    public function mount()
    {
        parent::mount();
        $userParameter = UsrUserParameter::where('usr_user_id', $this->userId)->first();
        $this->level = $userParameter->level;
        $this->exp = $userParameter->exp;
        $this->coin = $userParameter->coin;
        $this->stamina = $userParameter->stamina;
        $this->staminaUpdatedAt = $userParameter->stamina_updated_at;

        $this->form->fill([
            'level' => $this->level,
            'exp' => $this->exp,
            'coin' => $this->coin,
            'stamina' => $this->stamina,
            'staminaUpdatedAt' => $this->staminaUpdatedAt,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserParameter::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('level')->label('レベル'),
            TextInput::make('exp')->label('経験値'),
            TextInput::make('coin')->label('コイン'),
            TextInput::make('stamina')->label('スタミナ'),
            DateTimePicker::make('staminaUpdatedAt')->label('スタミナ更新日時'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrUserParameter::query()
            ->where('usr_user_id', $this->userId)
            ->update([
                'level' => $this->form->getState()['level'],
                'exp' => $this->form->getState()['exp'],
                'coin' => $this->form->getState()['coin'],
                'stamina' => $this->form->getState()['stamina'],
                'stamina_updated_at' => $this->form->getState()['staminaUpdatedAt'],
            ]);
        $this->redirectRoute('filament.admin.pages.user-parameter', ['userId' => $this->userId]);
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
