<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource\Pages;

use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\Rules\Unique;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\MasterAssetReleaseConstants;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngAssetReleaseResource;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngAssetRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngAssetReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\ClientCompatibilityVersionUtility;

/**
 * アセットリリース新規作成画面
 */
class CreateMngAssetRelease extends CreateRecord
{
    protected static string $resource = MngAssetReleaseResource::class;

    // 連続して作成ボタンを非表示
    protected static bool $canCreateAnother = false;

    /**
     * フォーム作成
     *
     * @param Form $form
     *
     * @return Form
     */
    public function form(Form $form): Form
    {
        // 登録済みのリリース情報の中から最新のものを取得
        /** @var MngAssetReleaseService $service */
        $service = app()->make(MngAssetReleaseService::class);

        $maxReleaseKeyMngAssetRelease = $service->getMngAssetReleasesByApplyOrPending()
            // リリースキーでグルーピング
            ->groupBy('release_key')
            // iosとandroidで2件セットで登録されているものだけでフィルタリング
            ->filter(fn ($rows) => $rows->count() === 2)
            // グルーピングした階層を平坦化
            ->flatten()
            // リリースキーの最大値のリリース情報を取得
            ->sortByDesc(fn (MngAssetRelease $row) => $row->release_key)
            ->first();
        $maxReleaseKey = is_null($maxReleaseKeyMngAssetRelease)
            ? 0
            : $maxReleaseKeyMngAssetRelease->release_key;
        $maxClientVersion = is_null($maxReleaseKeyMngAssetRelease)
            ? null
            : $maxReleaseKeyMngAssetRelease->client_compatibility_version;

        // クライアント互換性バージョンのバリデーションコールバックメソッド(ここでは実行してない)
        $validationClientVersion = ClientCompatibilityVersionUtility::makeValidateClientCompatibilityVersion($maxClientVersion);

        return $form
            ->schema([
                Forms\Components\Select::make('platform')
                    ->required()
                    ->label('Platform')
                    ->placeholder('プラットフォームを選択してください')
                    ->options(MasterAssetReleaseConstants::PLATFORM_STRING_LIST)
                    ->rules([
                        fn () => function ($attribute, $value, Closure $fail) {
                            if (!array_key_exists($value, MasterAssetReleaseConstants::PLATFORM_STRING_LIST)) {
                                $fail('想定しないplatformが選択されています');
                            }
                        }
                    ])
                    ->live(),
                Forms\Components\TextInput::make('release_key')
                    ->required()
                    ->placeholder('例: 202409260')
                    ->label('release_key')
                    ->numeric()
                    ->maxValue(MasterAssetReleaseConstants::MAX_RELEASE_KEY)
                    ->minValue($maxReleaseKey)
                    ->unique(modifyRuleUsing: function (Unique $rule, $get) {
                        $platform = (int) $get('platform');
                        if ($platform === MasterAssetReleaseConstants::PLATFORM_ALL) {
                            return $rule->whereIn('platform', [PlatformConstant::PLATFORM_IOS, PlatformConstant::PLATFORM_ANDROID]);
                        }
                        return $rule->where('platform', $platform);
                    })
                    ->validationMessages(['unique' => 'すでに登録済みのrelease_keyです']),
                Forms\Components\TextInput::make('client_compatibility_version')
                    ->required()
                    ->placeholder('例: 0.0.0')
                    ->label('クライアント互換性バージョン')
                    // 数字と.のみ許可するバリデーション
                    ->rules([
                        'regex:/^\d+\.\d+\.\d+$/',
                        fn () => function ($attribute, $value, Closure $fail) use ($validationClientVersion) {
                            // クライアント互換性バージョンのバリデーションを実行
                            $validationClientVersion($attribute, $value, $fail);
                        },
                    ])
                    ->validationMessages([
                        'regex' => '「数字.数字.数字」の形式で入力してください',
                    ]),
                Forms\Components\Textarea::make('description')
                    ->label('メモ')
                    ->placeholder('メモしたい内容があれば入力してください')
                    ->columnSpan(2)
                    ->rows(3),
                Forms\Components\DateTimePicker::make('start_at')
                    ->label('開始日時')
                    ->required()
                    ->reactive()
                    ->format('Y-m-d H:i:s'),
            ]);
    }

    /**
     * リダイレクトURLの設定
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * 作成ボタン実行
     *
     * @param bool $another
     * @return void
     * @throws \Filament\Support\Exceptions\Halt
     * @throws \Throwable
     */
    public function create(bool $another = false): void
    {
        $after = $this->form->getState();
        $platform = (int) $after['platform'];

        if ($platform !== MasterAssetReleaseConstants::PLATFORM_ALL) {
            // 全プラットフォーム以外なら通常の作成処理を実行
            parent::create($another);
            return;
        }

        // 全プラットフォームを選択した場合はそれぞれ登録する
        foreach ([PlatformConstant::PLATFORM_IOS, PlatformConstant::PLATFORM_ANDROID] as $platform) {
            MngAssetRelease::query()->create([
                'platform' => $platform,
                'release_key' => $after['release_key'],
                'client_compatibility_version' => $after['client_compatibility_version'],
                'description' => $after['description'],
                'start_at' => $after['start_at'],
            ]);
        }

        $this->getCreatedNotification()?->send();
        $this->redirect($this->getRedirectUrl());
    }
}
