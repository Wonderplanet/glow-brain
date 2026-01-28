<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrItem;
use App\Services\MstConfigService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserItem extends UserDataBasePage
{
    use Authorizable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::ITEM->value;

    public string $mstItemId = '';

    protected $queryString = [
        'userId',
        'mstItemId',
    ];

    public int $amount;

    public function mount()
    {
        parent::mount();
        $usrItem = UsrItem::where('usr_user_id', $this->userId)
            ->where('mst_item_id', $this->mstItemId)
            ->first();

        $this->amount = $usrItem?->amount ?? 0;

        $this->form->fill([
            'amount' => $this->amount,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserItem::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstItemId' => $this->mstItemId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        $mstConfigService = app(MstConfigService::class);

        return [
            TextInput::make('amount')
                ->label('所持数')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue($mstConfigService->getUserItemMaxAmount()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrItem::upsert(
            [
                'usr_user_id' => $this->userId,
                'mst_item_id' => $this->mstItemId,
                'amount' => $this->form->getState()['amount'],
            ],
            ['usr_user_id', 'mst_item_id'],
            ['amount'],
        );

        $this->redirectRoute('filament.admin.pages.user-item', ['userId' => $this->userId]);
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
