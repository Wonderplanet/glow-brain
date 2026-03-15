<?php

namespace App\Filament\Pages;

use App\Constants\GachaUpperType;
use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprStepupGacha;
use App\Models\Usr\UsrGacha;
use App\Models\Usr\UsrGachaUpper;
use App\Traits\NotificationTrait;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Support\Collection;

class EditUserGacha extends UserDataBasePage
{
    use Authorizable;
    use NotificationTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.common.update-form-page';
    public string $currentTab = UserSearchTabs::GACHA->value;

    // ページを特定できる一意の情報
    public string $oprGachaId = '';

    protected $queryString = [
        'userId',
        'oprGachaId',
    ];

    // formパラメータ
    public int $count = 0;
    public int $daily_count = 0;
    public ?string $played_at = null;
    public int $ad_count = 0;
    public int $ad_daily_count = 0;
    public ?string $ad_played_at = null;

    public int $upper_type_max_rarity_count = 0;
    public int $upper_type_pickup_count = 0;

    // ステップアップガシャ用
    public ?int $current_step_number = null;
    public ?int $loop_count = null;

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            UserGacha::getUrl(['userId' => $this->userId]) => $this->currentTab,
            self::getUrl(['userId' => $this->userId, 'oprGachaId' => $this->oprGachaId]) => '編集',
        ]);

        $this->setFormValues();
    }

    private function setFormValues(): void
    {
        $usrGacha = $this->getUsrGacha();

        if ($usrGacha === null) {
            $this->redirect(
                UserGacha::getUrl(['userId' => $this->userId]),
            );
            $this->sendDangerNotification(
                title: 'データが見つかりませんでした',
                body: 'ガシャID: ' . $this->oprGachaId,
            );
            return;
        }

        $this->count = $usrGacha->count;
        $this->daily_count = $usrGacha->daily_count;
        $this->played_at = $usrGacha->played_at;
        $this->ad_count = $usrGacha->ad_count;
        $this->ad_daily_count = $usrGacha->ad_daily_count;
        $this->ad_played_at = $usrGacha->ad_played_at;
        $this->current_step_number = $usrGacha->current_step_number;
        $this->loop_count = $usrGacha->loop_count;

        $usrGachaUppers = $this->getUsrGachaUppers();
        $this->upper_type_max_rarity_count = $usrGachaUppers->get(GachaUpperType::MAX_RARITY->value)?->count ?? 0;
        $this->upper_type_pickup_count = $usrGachaUppers->get(GachaUpperType::PICKUP->value)?->count ?? 0;
    }

    private function getUsrGacha(): ?UsrGacha
    {
        return UsrGacha::query()
            ->where('usr_user_id', $this->userId)
            ->where('opr_gacha_id', $this->oprGachaId)
            ->first();
    }

    private function getOprGacha(): ?OprGacha
    {
        return OprGacha::find($this->oprGachaId);
    }

    private function getOprStepupGacha(): ?OprStepupGacha
    {
        return OprStepupGacha::query()
            ->where('opr_gacha_id', $this->oprGachaId)
            ->first();
    }

    /**
     * @return Collection<string, UsrGachaUpper> key: upper_type
     */
    private function getUsrGachaUppers(): Collection
    {
        $oprGacha = $this->getOprGacha();
        if ($oprGacha === null) {
            return collect();
        }
        $oprGachaEntity = $oprGacha->toEntity();
        if ($oprGachaEntity->hasUpper() === false) {
            return collect();
        }

        return UsrGachaUpper::query()
            ->where('usr_user_id', $this->userId)
            ->where('upper_group', $oprGachaEntity->getUpperGroup())
            ->get()
            ->keyBy(function (UsrGachaUpper $usrGachaUpper) {
                return $usrGachaUpper->upper_type;
            });
    }

    public function form(Form $form): Form
    {
        $oprGachaEntity = $this->getOprGacha()?->toEntity();

        return $form
            ->schema([
                Section::make('通常で引く')->schema([
                    TextInput::make('count')->label('引いた回数')->numeric(),
                    TextInput::make('daily_count')->label('日次で引いた回数')->numeric(),
                    DateTimePicker::make('played_at')->label('最後に引いた時間'),
                ])->columns(2),

                Section::make('広告で引く')
                    // ステップアップガシャの時は表示しない
                    ->visible(function () use ($oprGachaEntity) {
                        return $oprGachaEntity?->isStepup() !== true;
                    })
                    ->schema([
                        TextInput::make('ad_count')->label('引いた回数')->numeric(),
                        TextInput::make('ad_daily_count')->label('日次で引いた回数')->numeric(),
                        DateTimePicker::make('ad_played_at')->label('最後に引いた時間'),
                    ])
                    ->columns(2),

                Section::make('天井グループ')
                    // 天井設定がある時のみ表示する
                    ->visible(function () use ($oprGachaEntity) {
                        return $oprGachaEntity?->hasUpper() ?? false;
                    })
                    ->schema([
                        TextInput::make('upper_type_max_rarity_count')->label('SSR天井')->numeric(),
                        TextInput::make('upper_type_pickup_count')->label('ピックアップ天井')->numeric(),
                    ])
                    ->columns(2),

                Section::make('ステップアップガシャ')
                    // ステップアップガシャの時のみ表示する
                    ->visible(function () use ($oprGachaEntity) {
                        return $oprGachaEntity?->isStepup() ?? false;
                    })
                    ->schema([
                        TextInput::make('current_step_number')
                            ->label('現在のステップ番号')
                            ->numeric()
                            ->minValue(1)
                            ->helperText(function () {
                                $oprStepupGacha = $this->getOprStepupGacha();
                                if ($oprStepupGacha) {
                                    return '最大ステップ数: ' . $oprStepupGacha->max_step_number;
                                }
                                return null;
                            }),
                        TextInput::make('loop_count')
                            ->label('周回数')
                            ->numeric()
                            ->minValue(0)
                            ->helperText(function () {
                                $oprStepupGacha = $this->getOprStepupGacha();
                                if ($oprStepupGacha) {
                                    $maxLoop = $oprStepupGacha->max_loop_count;
                                    return $maxLoop === null ? '最大周回数: 無制限' : '最大周回数: ' . $maxLoop;
                                }
                                return null;
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('update')
                ->label('更新')
                ->requiresConfirmation()
                ->action(fn () => $this->update()),
        ];
    }

    public function update()
    {
        $state = $this->form->getState();

        $this->updateUsrGacha($state);
        $this->upsertUsrGachaUppers($state);

        $this->redirect(
            UserGacha::getUrl(['userId' => $this->userId]),
        );
    }

    private function updateUsrGacha(array $state): void
    {
        $usrGacha = $this->getUsrGacha();
        $usrGacha->count = $state['count'] ?? 0;
        $usrGacha->daily_count = $state['daily_count'] ?? 0;
        $usrGacha->played_at = $state['played_at'] ?? null;
        $usrGacha->ad_count = $state['ad_count'] ?? 0;
        $usrGacha->ad_daily_count = $state['ad_daily_count'] ?? 0;
        $usrGacha->ad_played_at = $state['ad_played_at'] ?? null;
        $usrGacha->current_step_number = $state['current_step_number'] ?? null;
        $usrGacha->loop_count = $state['loop_count'] ?? null;
        $usrGacha->save();
    }

    private function upsertUsrGachaUppers(array $state): void
    {
        $oprGacha = $this->getOprGacha();
        if (is_null($oprGacha)) {
            return;
        }

        $usrGachaUppers = $this->getUsrGachaUppers();

        if (isset($state['upper_type_max_rarity_count'])) {
            $usrGachaUpper = $usrGachaUppers->get(GachaUpperType::MAX_RARITY->value);
            if (is_null($usrGachaUpper)) {
                $usrGachaUpper = new UsrGachaUpper();
                $usrGachaUpper->init($this->userId, $oprGacha->upper_group, GachaUpperType::MAX_RARITY->value);
            }
            $usrGachaUpper->count = $state['upper_type_max_rarity_count'];
            $usrGachaUpper->save();
        }

        if (isset($state['upper_type_pickup_count'])) {
            $usrGachaUpper = $usrGachaUppers->get(GachaUpperType::PICKUP->value);
            if (is_null($usrGachaUpper)) {
                $usrGachaUpper = new UsrGachaUpper();
                $usrGachaUpper->init($this->userId, $oprGacha->upper_group, GachaUpperType::PICKUP->value);
            }
            $usrGachaUpper->count = $state['upper_type_pickup_count'];
            $usrGachaUpper->save();
        }
    }
}
