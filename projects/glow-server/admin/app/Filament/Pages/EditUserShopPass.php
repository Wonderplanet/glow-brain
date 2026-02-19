<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrShopPass;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class EditUserShopPass extends UserDataBasePage
{
    use Authorizable;
    protected static string $view = 'filament.common.update-form-page';

    public string $currentTab = UserSearchTabs::SHOP_PASS->value;

    public string $mstShopPassId = '';

    protected $queryString = [
        'userId',
        'mstShopPassId',
    ];

    public int $daily_reward_received_count;
    public ?string $daily_latest_received_at;
    public ?string $start_at;
    public ?string $end_at;

    public function mount()
    {
        parent::mount();
        $now = CarbonImmutable::now()->toDateTimeString();

        /** @var UsrShopPass|null $usrShopItem */
        $usrShopPass = UsrShopPass::where('usr_user_id', $this->userId)
            ->where('mst_shop_pass_id', $this->mstShopPassId)
            ->first();

        $this->daily_reward_received_count = $usrShopPass?->daily_reward_received_count ?? 0;
        $this->daily_latest_received_at = $usrShopPass?->daily_latest_received_at ?? $now;
        $this->start_at = $usrShopPass?->start_at ?? $now;
        $this->end_at = $usrShopPass?->end_at ?? $now;

        $this->form->fill([
            'daily_reward_received_count' => $this->daily_reward_received_count,
            'daily_latest_received_at' => $this->daily_latest_received_at,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
        ]);

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserShopPass::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'mstShopPassId' => $this->mstShopPassId]) => '編集',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('daily_reward_received_count')
                ->label('毎日報酬受け取り回数')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(9999),
            DateTimePicker::make('daily_latest_received_at')
                ->label('毎日報酬受け取り日時'),
            DateTimePicker::make('start_at')
                ->label('開始日時')
                ->required(),
            DateTimePicker::make('end_at')
                ->label('終了日時')
                ->required(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    public function update()
    {
        UsrShopPass::query()
            ->where('usr_user_id', $this->userId)
            ->where('mst_shop_pass_id', $this->mstShopPassId)
            ->update([
                'daily_reward_received_count' => $this->form->getState()['daily_reward_received_count'],
                'daily_latest_received_at' => $this->form->getState()['daily_latest_received_at'],
                'start_at' => $this->form->getState()['start_at'],
                'end_at' => $this->form->getState()['end_at'],
            ]);

        $this->redirectRoute('filament.admin.pages.user-shop-pass', ['userId' => $this->userId]);
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
