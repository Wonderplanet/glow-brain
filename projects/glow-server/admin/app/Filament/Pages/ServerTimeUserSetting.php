<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\Debug\Entities\DebugUserTimeSetting;
use App\Domain\Debug\Repositories\DebugUserAllTimeSettingRepository;
use App\Domain\Debug\Repositories\DebugUserTimeSettingRepository;
use App\Utils\TimeUtil;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ServerTimeUserSetting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.server-time-user-setting';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static ?string $title = 'ユーザーサーバ時間設定';
    protected static ?string $slug = 'server-time-user-setting';
    // メニューに出さない
    protected static bool $shouldRegisterNavigation = false;

    // 時間フォーマット
    private const DATETIME_FORMAT = 'Y年m月d日 H時i分s秒';
    // 入力タイムゾーン
    private const INPUT_TIMEZONE = 'Asia/Tokyo';

    // フォームで入力した値が以下のプロパティに保存される
    public ?string $userTime = null;

    public string $userId = '';

    protected $queryString = [
        'userId',
    ];

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $this->validate([
            'userId' => 'required|string',
        ]);

        $currentDateTime = new CarbonImmutable();
        $debugUserTimeSettingRepository = app()->make(DebugUserTimeSettingRepository::class);
        $debugSetting = $debugUserTimeSettingRepository->get($this->userId);
        if (isset($debugSetting)) {
            $now = CarbonImmutable::instance(new \DateTime('now', new \DateTimeZone(self::INPUT_TIMEZONE)));
            $currentDateTime = $debugSetting->getUserTime($now);
        }

        // ユーザの時間変更がなければ、全体設定を取得
        $debugUserAllTimeSettingRepository = app()->make(DebugUserAllTimeSettingRepository::class);
        $debugAllSetting = $debugUserAllTimeSettingRepository->get($this->userId);
        if (!isset($debugSetting) && isset($debugAllSetting)) {
            $now = CarbonImmutable::instance(new \DateTime('now', new \DateTimeZone(self::INPUT_TIMEZONE)));
            $currentDateTime = $debugAllSetting->getUserAllTime($now);
        }

        return $infolist
            ->state([
                'currentDateTime' => $currentDateTime,
            ])
            ->schema([
                Grid::make([
                    'default' => 1,
                ])->schema([
                    TextEntry::make('currentDateTime')
                        ->label('サーバー現在日時（JST）')
                        ->icon('heroicon-o-clock')
                        ->dateTime(self::DATETIME_FORMAT)
                        ->formatStateUsing(fn(string $state): string => TimeUtil::formatJapanese($state)),
                ])
            ]);
    }

    public function Form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('userTime')
                ->label('変更日時（JST）')
                ->seconds(false)
                ->required(),
            ]);
    }

    public function setTimeSetting(): void
    {
        // フォームの値の取得
        $userTime = $this->userTime;

        if (is_null($userTime)) {
            Notification::make()
            ->title('変更日時を入力してください')
            ->info()
            ->send();

            return;
        }

        $targetDateTime = new CarbonImmutable($userTime, self::INPUT_TIMEZONE);

        // デバッグユーザー時間設定を設定
        $debugUserTimeSetting = new DebugUserTimeSetting($targetDateTime);
        $debugUserTimeSettingRepository = app()->make(DebugUserTimeSettingRepository::class);
        $debugUserTimeSettingRepository->put($this->userId, $debugUserTimeSetting);

        Notification::make()
            ->title('ユーザーサーバー日時を設定しました')
            ->success()
            ->send();
    }

    public function resetTimeSetting(): void
    {
        // デバッグユーザー時間設定が未設定
        $debugUserTimeSettingRepository = app()->make(DebugUserTimeSettingRepository::class);
        $existsSetting = $debugUserTimeSettingRepository->exists($this->userId);
        if (! $existsSetting) {
            Notification::make()
            ->title('ユーザーサーバー日時の設定が未登録でした')
            ->success()
            ->send();

            return;
        }

        // デバッグユーザー時間設定を削除
        $debugUserTimeSettingRepository->delete($this->userId);

        Notification::make()
            ->title('ユーザーサーバー日時をリセットしました')
            ->success()
            ->send();
    }
}
