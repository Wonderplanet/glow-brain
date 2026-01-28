<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrStoreProduct;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserStoreProduct extends UserDataBasePage
{
    use Authorizable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::SHOP_PURCHASE->value;

    public string $productSubId = '';

    protected $queryString = [
        'userId',
        'productSubId',
    ];

    public int $purchase_count;
    public ?string $last_reset_at;

    public function mount()
    {
        parent::mount();
        /** @var UsrStoreProduct|null $usrStoreProduct */
        $usrStoreProduct = UsrStoreProduct::where('usr_user_id', $this->userId)
            ->where('product_sub_id', $this->productSubId)
            ->first();

        $this->purchase_count = $usrStoreProduct?->purchase_count ?? 0;
        $this->last_reset_at = $usrStoreProduct?->last_reset_at ?? CarbonImmutable::now()->toDateTimeString();

        $this->form->fill([
            'purchase_count' => $this->purchase_count,
            'last_reset_at' => $this->last_reset_at,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserStoreProduct::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'productSubId' => $this->productSubId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('purchase_count')
                ->label('購入回数')
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
        UsrStoreProduct::query()
            ->where('usr_user_id', $this->userId)
            ->where('product_sub_id', $this->productSubId)
            ->update([
                'purchase_count' => $this->form->getState()['purchase_count'],
                'last_reset_at' => $this->form->getState()['last_reset_at'],
            ]);

        $this->redirectRoute('filament.admin.pages.user-store-product', ['userId' => $this->userId]);
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
