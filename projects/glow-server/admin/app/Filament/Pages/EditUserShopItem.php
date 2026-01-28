<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrShopItem;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserShopItem extends UserDataBasePage
{
    use Authorizable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::SHOP_BASIC->value;

    public string $mstShopItemId = '';

    protected $queryString = [
        'userId',
        'mstShopItemId',
    ];

    public int $trade_count;
    public ?string $last_reset_at;

    public function mount()
    {
        parent::mount();

        /** @var UsrShopItem|null $usrShopItem */
        $usrShopItem = UsrShopItem::where('usr_user_id', $this->userId)
            ->where('mst_shop_item_id', $this->mstShopItemId)
            ->first();

        $this->trade_count = $usrShopItem?->trade_count ?? 0;
        $this->last_reset_at = $usrShopItem?->last_reset_at ?? CarbonImmutable::now()->toDateTimeString();

        $this->form->fill([
            'trade_count' => $this->trade_count,
            'last_reset_at' => $this->last_reset_at,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserShopItem::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstShopItemId' => $this->mstShopItemId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('trade_count')
                ->label('交換回数')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(9999),
            DateTimePicker::make('last_reset_at')
                ->label('最終リセット日時')
                ->required(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrShopItem::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_shop_item_id', $this->mstShopItemId)
            ->update([
                'trade_count' => $this->form->getState()['trade_count'],
                'last_reset_at' => $this->form->getState()['last_reset_at'],
            ]);

        $this->redirectRoute('filament.admin.pages.user-shop-item', ['userId' => $this->userId]);
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
