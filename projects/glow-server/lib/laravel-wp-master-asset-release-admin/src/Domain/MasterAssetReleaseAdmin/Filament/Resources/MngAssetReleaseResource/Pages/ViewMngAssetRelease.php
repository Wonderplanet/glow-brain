<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages;

use App\Traits\MngCacheDeleteTrait;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use WonderPlanet\Domain\Admin\Trait\DatabaseTransactionTrait;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\ClientCompatibilityVersionUtility;

class ViewMngAssetRelease extends Page
{
    use InteractsWithFormActions;
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $resource = MngAssetReleaseResource::class;

    protected static string $view = 'view-master-asset-admin::filament.resources.mng-asset-release-resource.pages.view-mng-assert-release';

    public MngAssetRelease|null $mngAssetRelease = null;
    private string $mngAssetReleaseId = '';
    public string $clientCompatibilityVersion = '';
    public string $platformStr = '';
    public string|null $description = null;
    public ?string $startAt;

    /**
     * 画面遷移時に初回だけ起動
     *
     * @return void
     */
    public function mount(string $record): void
    {
        $this->mngAssetReleaseId = $record;
        $this->mngAssetRelease = MngAssetRelease::query()->find($this->mngAssetReleaseId);

        $clientCompatibilityVersion = '';
        $description = '';
        $platformStr = '';
        if (!is_null($this->mngAssetRelease)) {
            $clientCompatibilityVersion = $this->mngAssetRelease?->client_compatibility_version ?? '';
            $description = $this->mngAssetRelease?->description ?? '';
            $platformStr = PlatformConstant::PLATFORM_STRING_LIST[$this->mngAssetRelease->platform];
        }
        $this->clientCompatibilityVersion = $clientCompatibilityVersion;
        $this->description = $description;
        $this->platformStr = $platformStr;
        $this->startAt = $this->mngAssetRelease?->start_at?->toDateTimeString() ?? null;
    }

    /**
     * @return string|Htmlable
     */
    public function getTitle(): string|Htmlable
    {
        $releaseKey = $this->mngAssetRelease?->release_key;
        return "配信管理ダッシュボード/{$releaseKey}";
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        // 登録済みのリリース情報の中から最新のものを取得
        /** @var MngAssetReleaseService $service */
        $service = app()->make(MngAssetReleaseService::class);
        $maxReleaseKeyMngAssetRelease = $service->getMngAssetReleasesByApplyOrPending()
            ->reject(fn (MngAssetRelease $row) => $row->release_key >= $this->mngAssetRelease->release_key)
            ->sortByDesc(fn (MngAssetRelease $row) => $row->release_key)
            ->first();
        $maxClientVersion = is_null($maxReleaseKeyMngAssetRelease)
            ? null
            : $maxReleaseKeyMngAssetRelease->client_compatibility_version;

        // クライアント互換性バージョンのバリデーションコールバックメソッド(ここでは実行してない)
        $validationClientVersion = ClientCompatibilityVersionUtility::makeValidateClientCompatibilityVersion($maxClientVersion);

        return $form->schema([
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('platformStr')
                        ->label('プラットフォーム')
                        ->columnSpan(1)
                        ->disabled(),
                ]),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('clientCompatibilityVersion')
                        ->required()
                        ->placeholder('例: 0.0.0')
                        ->label(fn () => new HtmlString("<span class='font-bold'>クライアント互換性バージョン</span>"))
                        // 数字と.のみ許可するバリデーション
                        ->rules([
                            'regex:/^\d+\.\d+\.\d+$/',
                            fn () => function ($attribute, $value, Closure $fail) use ($validationClientVersion) {
                                $validationClientVersion($attribute, $value, $fail);
                            },
                        ]),
                ]),
            Forms\Components\Textarea::make('description')
                ->label('メモ')
                ->placeholder('メモしたい内容があれば入力してください')
                ->columnSpan(2)
                ->rows(3),
            Forms\Components\DateTimePicker::make('startAt')
                ->label('開始日時')
                ->required()
                ->reactive()
                ->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * メモ更新を実行
     *
     * @return void
     * @throws \Throwable
     */
    public function update(): void
    {
        // バリデーション実行
        $this->validate(
            messages: [
                'clientCompatibilityVersion.required' => 'クライアント互換性バージョンは入力必須です',
                'clientCompatibilityVersion.regex' => '「数字.数字.数字」の形式で入力してください',
            ]
        );

        try {
            $this->transaction(function () {
                // 入力値が空の場合はnullで更新する(値があればそのまま更新する)
                $description = empty($this->description) ? null : $this->description;
                $this->mngAssetRelease->description = $description;
                $this->mngAssetRelease->client_compatibility_version = $this->clientCompatibilityVersion;
                $this->mngAssetRelease->start_at = $this->startAt;
                $this->mngAssetRelease->save();

                // mng_asset_release_versionsのキャッシュを削除
                $this->deleteMngAssetReleaseVersionCache();
            }, [DBUtility::getMngConnName()]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('メモの更新に失敗しました。')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->send();
            Log::error('', [$e]);
            return;
        }

        Notification::make()
            ->title('更新しました')
            ->success()
            ->send();
    }

    /**
     * @return array|Action[]|\Filament\Actions\ActionGroup[]
     */
    protected function getActions(): array
    {
        return [
            $this->deleteButton(),
        ];
    }

    /**
     * 削除ボタン
     *
     * @return Action
     */
    public function deleteButton(): Action
    {
        return Action::make('delete')
            ->label(function () {
                if ($this->mngAssetRelease?->enabled) {
                    // enabledがtrueなら削除できないメッセージを表示
                    return '配信済みのリリースキーは削除できません';
                }
                return '配信情報の削除';
            })
            ->requiresConfirmation()
            ->action(fn () => $this->delete())
            // enabledがtrueなら削除させない
            ->disabled(fn () => $this->mngAssetRelease?->enabled);
    }

    /**
     * 削除実行
     *
     * @return void
     */
    private function delete(): void
    {
        $isError = false;

        try {
            $this->transaction(function () {
                /** @var MngAssetReleaseService $mngAssetReleaseService */
                $mngAssetReleaseService = app()->make(MngAssetReleaseService::class);

                $mngAssetReleaseService->deleteAssetRelease(
                    $this->mngAssetRelease
                );

                // mng_asset_release_versionsのキャッシュを削除
                $this->deleteMngAssetReleaseVersionCache();
            }, [DBUtility::getMngConnName()]);
        } catch (\Exception $e) {
            $isError = true;
            Notification::make()
                ->title('リリースキーの削除に失敗しました。')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->send();
            Log::error('', [$e]);
        }

        if (!$isError) {
            Notification::make()
                ->title('削除しました')
                ->body("リリースキー：{$this->mngAssetRelease?->release_key}")
                ->success()
                ->send();
        }

        redirect()->to('/admin/mng-asset-releases');
    }
}
