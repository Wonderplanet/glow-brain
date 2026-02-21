<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseImportResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;

/**
 * アセットインポート画面
 */
class ImportMngAssetRelease extends CreateRecord
{
    protected static string $resource = MngAssetReleaseImportResource::class;

    // 連続して作成ボタンを非表示
    protected static bool $canCreateAnother = false;

    protected static ?string $title = 'アセット配信管理ダッシュボード 環境間インポート';
    protected static ?string $breadcrumb = 'アセット環境間インポート';

    public ?string $fromEnvironment = null;
    public ?string $releaseKeyIos = '0';
    public ?string $releaseKeyAndroid = '0';

    /**
     * @var MngAssetReleaseService
     */
    private MngAssetReleaseService $service;

    public function __construct() {
        $this->service = app()->make(MngAssetReleaseService::class);
    }

    /**
     * フォーム作成
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        // 最新のリリースキーを取得
        $latestReleaseKeyAndroid = $this->service->getLatestReleaseKey(PlatformConstant::PLATFORM_ANDROID);
        $latestReleaseKeyIos = $this->service->getLatestReleaseKey(PlatformConstant::PLATFORM_IOS);

        // 取り込み可能な環境一覧
        $importableFromEnvironmentList = $this->service->getImportEnvironment();

        return $form
            ->schema([
                Forms\Components\Radio::make('fromEnvironment')
                    ->label('インポート元環境の選択')
                    ->required()
                    ->options($importableFromEnvironmentList)
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function ($state) use ($importableFromEnvironmentList) {
                        $this->fromEnvironment = $importableFromEnvironmentList[$state];
                    }),
                Forms\Components\Radio::make('releaseKeyIos')
                    ->label('インポート元環境のReleaseKey ( iOS )')
                    ->helperText('インポート元環境選択後、対象が表示されます')
                    ->options(function () use ($latestReleaseKeyIos) {
                        return $this->makeReleaseKeyList(PlatformConstant::PLATFORM_IOS, $this->fromEnvironment, $latestReleaseKeyIos);
                    })
                    // デフォルトは、「選択しない」
                    ->default('0')
                    ->afterStateUpdated(function ($state) {
                        $this->releaseKeyIos = $state;
                    })
                    ->live()
                    ->columnSpanFull(),
                Forms\Components\Radio::make('releaseKeyAndroid')
                    ->label('インポート元環境のReleaseKey ( Android )')
                    ->helperText('インポート元環境選択後、対象が表示されます')
                    ->options(function () use ($latestReleaseKeyAndroid) {
                        return $this->makeReleaseKeyList(PlatformConstant::PLATFORM_ANDROID, $this->fromEnvironment, $latestReleaseKeyAndroid);
                    })
                    // デフォルトは、「選択しない」
                    ->default('0')
                    ->afterStateUpdated(function ($state) {
                        $this->releaseKeyAndroid = $state;
                    })
                    ->live()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * リダイレクト設定
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('confirm', [
            'releaseKeyIos' => $this->releaseKeyIos,
            'releaseKeyAndroid' => $this->releaseKeyAndroid,
            'fromEnvironment' => $this->fromEnvironment,
        ]);
    }

    /**
     * 各ボタンのアクション
     *
     * @return array
     */
    public function getFormActions(): array
    {
        return [
            Actions\Action::make('list')
                ->label('戻る')
                ->color('gray')
                ->url(self::getResource()::getUrl('index')),
            Actions\Action::make('confirm')->label('次へ')
                ->action(fn () => $this->next()),
        ];
    }

    /**
     * 確認画面遷移前のチェック
     *
     * @return void
     */
    public function next(): void
    {
        // 入力値のバリデーション
        // バリデーションメッセージ設定
        $validated = $this->validate(
            messages: [
                'data.fromEnvironment.required' => 'インポート元環境の選択は必須です',
            ]
        )['data'];

        // リリースキーはiOSとAndroidどちらかは選択する必要がある
        if ($validated['releaseKeyIos'] === '0' && $validated['releaseKeyAndroid'] === '0') {
            Notification::make()
                ->title('リリースキーはどちらかは必ず指定してください')
                ->color('danger')
                ->send();
            return;
        }

        $this->redirect($this->getRedirectUrl());
    }

    /**
     * リリースキー一覧の生成
     *
     * @param int $platform
     * @param string|null $fromEnvironment // インポート元環境名
     * @param int|null $latestReleaseKey
     *
     * @return Collection
     */
    private function makeReleaseKeyList(int $platform, ?string $fromEnvironment, ?int $latestReleaseKey): Collection
    {
        $textNotFoundReleaseKey = "選択しない(対象のrelease_keyなし)";
        $textNoSelect = "選択しない";

        $result = collect();

        // インポート元の環境を指定していないときは表示させない
        if (is_null($fromEnvironment)) {
            return $result;
        }

        // 操作中環境のリリースキー一覧
        $releaseKeyList = $this->service->getEffectiveAssetReleaseList($platform, $latestReleaseKey)->pluck('release_key');

        // インポート元環境のリリースキー一覧を取得
        $releaseKeyListFromEnvironment = $this->service->getEffectiveReleaseKeyListFromEnvironment(
            $fromEnvironment,
            $platform,
        );
        $importList = [];
        foreach ($releaseKeyListFromEnvironment as $value) {
            $importList[$value['release_key']] = $value['description'];
        }

        // 操作中環境と、インポート元環境で一致したリリースキーのみ残す
        $result = $releaseKeyList
            ->intersect(array_keys($importList))
            ->mapWithKeys(function ($item) use ($importList) {
                return [$item => "{$item}:{$importList[$item]}"];
            });

        if ($result->isEmpty()) {
            // 存在しない場合は
            $result->prepend($textNotFoundReleaseKey, '0');
        } else {
            $result->prepend($textNoSelect, '0');
        }

        return $result;
    }
}
