<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Services\UserDeviceSwapService;
use App\Models\Usr\UsrUser;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class UserDeviceSwap extends Page implements HasForms
{
    use InteractsWithForms;
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static string $view = 'filament.pages.user-device-swap';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static ?int $navigationSort = 999;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'ユーザー切り替え';
    }

    public function getTitle(): string|Htmlable
    {
        return 'ユーザー切り替え';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function updated($name, $value): void
    {
        // フォームデータが更新されたときにページを再描画
        if (str_starts_with($name, 'data.user') && str_ends_with($name, '_my_id')) {
            $this->dispatch('$refresh');
        }
    }

    public function form(Form $form): Form
    {
        $descriptionHtml = <<<HTML
        <div class="text-sm text-gray-500">
            端末で使用中の「今のユーザー」を「切替後ユーザー」に切り替えます。<br>
            再ログインすることで切替後ユーザーとしてプレイできるようになります。<br>
            <br>
            ※ 実装補足: sign_in APIを実行してアクセストークンを取り直すことで、切替後ユーザーとしてプレイできるようになります。
        </div>
        HTML;

        return $form
            ->schema([
                Section::make('ユーザー選択')
                    ->description(new HtmlString($descriptionHtml))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('user1_search')
                                    ->label('今のユーザー マイID')
                                    ->placeholder('マイIDを入力してください')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (empty($state)) {
                                            $set('user1_id', null);
                                            return;
                                        }

                                        try {
                                            $service = app(UserDeviceSwapService::class);
                                            $usrUsers = $service->searchUsers($state);

                                            if ($usrUsers->count() === 1) {
                                                $usrUser = $usrUsers->first();
                                                if ($usrUser->profile) {
                                                    $set('user1_id', $usrUser->profile->my_id . '（' . $usrUser->profile->name . '）');
                                                    $set('user1_my_id', $usrUser->profile->my_id);
                                                }
                                            } else {
                                                $set('user1_id', null);
                                                $set('user1_my_id', null);
                                            }
                                        } catch (\Exception $e) {
                                            $set('user1_id', null);
                                            $set('user1_my_id', null);
                                        }
                                    }),

                                TextInput::make('user2_search')
                                    ->label('切替後ユーザー マイID')
                                    ->placeholder('マイIDを入力してください')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (empty($state)) {
                                            $set('user2_id', null);
                                            return;
                                        }

                                        try {
                                            $service = app(UserDeviceSwapService::class);
                                            $usrUsers = $service->searchUsers($state);

                                            if ($usrUsers->count() === 1) {
                                                $usrUser = $usrUsers->first();
                                                if ($usrUser->profile) {
                                                    $set('user2_id', $usrUser->profile->my_id . '（' . $usrUser->profile->name . '）');
                                                    $set('user2_my_id', $usrUser->profile->my_id);
                                                }
                                            } else {
                                                $set('user2_id', null);
                                                $set('user2_my_id', null);
                                            }
                                        } catch (\Exception $e) {
                                            $set('user2_id', null);
                                            $set('user2_my_id', null);
                                        }
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('user1_id')
                                    ->label('今のユーザー 情報')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('user2_id')
                                    ->label('切替後ユーザー 情報')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        // 隠しフィールド（実際の処理で使用するmy_id）
                        Hidden::make('user1_my_id'),
                        Hidden::make('user2_my_id'),
                    ]),
            ])
            ->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('swap')
                ->label('ユーザー切り替え実行')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('ユーザー切り替えの確認')
                ->modalDescription('本当にユーザーを切り替えますか？この操作は取り消すことができません。')
                ->modalSubmitActionLabel('実行')
                ->action('executeSwap')
                ->disabled(function () {
                    return empty($this->data['user1_my_id']) || empty($this->data['user2_my_id']);
                })
                ->extraAttributes(['wire:loading.attr' => 'disabled']),
        ];
    }


    public function executeSwap(): void
    {
        try {
            $data = $this->form->getState();

            if (!isset($data['user1_my_id']) || !isset($data['user2_my_id']) ||
                empty($data['user1_my_id']) || empty($data['user2_my_id'])) {
                Notification::make()
                    ->title('エラー')
                    ->body('両方のユーザーを選択してください。利用可能なデータ: ' . json_encode(array_keys($data)))
                    ->danger()
                    ->send();
                return;
            }

            $service = app(UserDeviceSwapService::class);
            $result = $service->swapUserDevices(
                $data['user1_my_id'],
                $data['user2_my_id']
            );

            if ($result) {
                Notification::make()
                    ->title('ユーザー切り替え完了')
                    ->body('ユーザーの切り替えが正常に完了しました。')
                    ->success()
                    ->send();

                // フォームをリセット
                $this->data = [];
                $this->form->fill();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('エラー')
                ->body('ユーザーの切り替えに失敗しました: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}

