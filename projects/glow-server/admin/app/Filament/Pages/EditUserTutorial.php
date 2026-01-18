<?php

namespace App\Filament\Pages;

use App\Constants\TutorialFunctionName;
use App\Constants\TutorialType;
use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstTutorial;
use App\Models\Usr\UsrUser;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserTutorial extends UserDataBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::TUTORIAL->value;

    public string $tutorialStatus;

    public function mount()
    {
        parent::mount();

        $usrUser = UsrUser::find($this->userId);
        $this->tutorialStatus = $usrUser->tutorial_status;

        $this->form->fill([
            'tutorialStatus' => $this->tutorialStatus,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserTutorial::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId]) => '編集',
        ]);
    }

    public function form(Form $form): Form
    {
        $mstTutorials = MstTutorial::query()
            ->whereIn(
                'type',
                [
                    TutorialType::INTRO->value,
                    TutorialType::MAIN->value,
                ]
            )
            ->get()
            ->sortBy('sort_order');

        $functionNames = collect(['' => '0: ' . TutorialFunctionName::NOT_PLAYED->label()]);
        foreach ($mstTutorials as $mstTutorial) {
            $functionName = $mstTutorial->function_name;

            $functionNames[$functionName] = sprintf(
                '%d: %s',
                $mstTutorial->sort_order,
                $functionName,
            );
        }

        return $form->schema(
            [
                Select::make('tutorialStatus')
                    ->label('メインパートまでの進捗状況')
                    ->options($functionNames),
            ]
        );
    }

    public function update()
    {
        UsrUser::query()
            ->where('id', $this->userId)
            ->update([
                'tutorial_status' => $this->form->getState()['tutorialStatus'] ?? '',
            ]);
        $this->redirectRoute('filament.admin.pages.user-tutorial', ['userId' => $this->userId]);
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
