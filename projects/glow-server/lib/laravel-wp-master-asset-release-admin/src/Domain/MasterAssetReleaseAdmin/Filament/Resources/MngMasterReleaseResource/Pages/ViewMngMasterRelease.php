<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages;

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
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators\MasterDataDBOperator;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\ClientCompatibilityVersionUtility;

class ViewMngMasterRelease extends Page
{
    use InteractsWithFormActions;
    use DatabaseTransactionTrait;

    protected static string $resource = MngMasterReleaseResource::class;

    protected static string $view = 'view-master-asset-admin::filament.resources.mng-master-release-resource.pages.view-mng-master-release';

    public MngMasterRelease|null $mngMasterRelease = null;
    private string $mngMasterReleaseId = '';
    public string $clientCompatibilityVersion = '';
    public string|null $description = null;
    public ?string $startAt;

    /**
     * 画面遷移時に初回だけ起動
     *
     * @return void
     */
    public function mount(string $record): void
    {
        $this->mngMasterReleaseId = $record;
        $this->mngMasterRelease = MngMasterRelease::query()->find($this->mngMasterReleaseId);
        $this->clientCompatibilityVersion = $this->mngMasterRelease->client_compatibility_version ?? '';
        $this->description = $this->mngMasterRelease?->description;
        $this->startAt = $this->mngMasterRelease?->start_at?->toDateTimeString() ?? null;
    }

    /**
     * @return string|Htmlable
     */
    public function getTitle(): string|Htmlable
    {
        $releaseKey = $this->mngMasterRelease?->release_key;
        return "マスターデータ配信管理ダッシュボード/{$releaseKey}";
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        /** @var MngMasterReleaseService $service */
        $service = app()->make(MngMasterReleaseService::class);
        $rejectedMngMasterRelease = $service->getMngMasterReleasesByApplyOrPending()
            // 詳細で表示しているリリース情報のrelease_key以上の情報を除き、その中で最新のリリースキーを取得
            // 指定したリリースキーより上のクライアントバージョンたちを修正する場合を考慮している
            ->reject(fn (MngMasterRelease $mngMasterRelease) => $mngMasterRelease->release_key >= $this->mngMasterRelease->release_key)
            ->first();
        $maxClientVersion = is_null($rejectedMngMasterRelease)
            ? null
            : $rejectedMngMasterRelease->client_compatibility_version;
        $validationClientVersion = ClientCompatibilityVersionUtility::makeValidateClientCompatibilityVersion($maxClientVersion);

        return $form->schema([
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
            Forms\Components\Textarea::make('description')
                ->label(fn () => new HtmlString("<span class='font-bold'>メモ</span>"))
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
     * 更新を実行
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

                $this->mngMasterRelease->client_compatibility_version = $this->clientCompatibilityVersion;
                $this->mngMasterRelease->description = $description;
                $this->mngMasterRelease->start_at = $this->startAt;
                $this->mngMasterRelease->save();
            }, [DBUtility::getMngConnName()]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('情報の更新に失敗しました。')
                ->body('サーバー管理者にお問い合わせください。')
                ->danger() // 通知のアイコンを指定
                ->color('danger') // 通知の背景色を指定
                ->send();
            Log::error('', [$e]);
            return;
        }

        Notification::make()
            ->title('情報を更新しました')
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
                if ($this->mngMasterRelease?->enabled) {
                    // enabledがtrueなら削除できないメッセージを表示
                    return '配信済みのリリースキーは削除できません';
                }
                return '配信情報の削除';
            })
            ->requiresConfirmation()
            ->action(fn () => $this->delete())
            // enabledがtrueなら削除させない
            ->disabled(fn () => $this->mngMasterRelease?->enabled);
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
                /** @var MngMasterReleaseService $mngMasterReleaseService */
                $mngMasterReleaseService = app()->make(MngMasterReleaseService::class);

                $mngMasterReleaseService->deleteMasterRelease(
                    $this->mngMasterRelease
                );
            }, [DBUtility::getMngConnName()]);

            // DB削除はトランザクションが効かないのでトランザクションの外で実行している
            if (!is_null($this->mngMasterRelease->mngMasterReleaseVersion)) {
                // リリースバージョンのDBを削除
                /** @var MngMasterReleaseVersion $mngMasterReleaseVersion */
                $mngMasterReleaseVersion = $this->mngMasterRelease->mngMasterReleaseVersion;

                // dbName取得用にパラメータを生成
                $serverDbHashMap = [
                    $mngMasterReleaseVersion->release_key => $mngMasterReleaseVersion->server_db_hash,
                ];
                /** @var MasterDataDBOperator $masterDataDbOperator */
                $masterDataDbOperator = app()->make(MasterDataDBOperator::class);
                $dbName = $masterDataDbOperator->getMasterDbName($mngMasterReleaseVersion->release_key, $serverDbHashMap);
                $masterDataDbOperator->drop($dbName);
            }
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
                ->body("リリースキー：{$this->mngMasterRelease?->release_key}")
                ->success()
                ->send();
        }

        redirect()->to('/admin/mng-master-releases');
    }
}
