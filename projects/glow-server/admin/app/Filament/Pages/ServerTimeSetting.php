<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Domain\Debug\Entities\DebugUserAllTimeSetting;
use App\Domain\Debug\Repositories\DebugUserAllTimeSettingRepository;
use App\Filament\Authorizable;
use App\Utils\TimeUtil;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ServerTimeSetting extends Page
{
    use Authorizable;
        
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.server-time-setting';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static ?string $title = 'サーバー時間変更';
    protected static ?string $slug = 'server-time-setting';

    // 時間フォーマット
    private const DATETIME_FORMAT = 'Y年m月d日 H時i分s秒';
    // 入力タイムゾーン
    private const INPUT_TIMEZONE = 'Asia/Tokyo';

    // フォームで入力した値が以下のプロパティに保存される
    public ?string $userAllTime = null;

    public function infolist(Infolist $infolist): Infolist
    {
        $currentDateTime = new CarbonImmutable();
        $debugUserAllTimeSettingRepository = app()->make(DebugUserAllTimeSettingRepository::class);
        $debugSetting = $debugUserAllTimeSettingRepository->get();
        if (isset($debugSetting)) {
            $now = CarbonImmutable::instance(new \DateTime('now', new \DateTimeZone(self::INPUT_TIMEZONE)));
            $currentDateTime = $debugSetting->getUserAllTime($now);
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
                DateTimePicker::make('userAllTime')
                ->label('変更日時（JST）')
                ->seconds(false)
                ->required(),
            ]);
    }

    public function setTimeSetting(): void
    {
        // フォームの値の取得
        $userAllTime = $this->userAllTime;

        if (is_null($userAllTime)) {
            Notification::make()
            ->title('変更日時を入力してください')
            ->info()
            ->send();

            return;
        }

        $targetDateTime = new CarbonImmutable($userAllTime, self::INPUT_TIMEZONE);

        // デバッグユーザー全体時間設定を設定
        $debugUserAllTimeSetting = new DebugUserAllTimeSetting($targetDateTime);
        $debugUserAllTimeSettingRepository = app()->make(DebugUserAllTimeSettingRepository::class);
        $debugUserAllTimeSettingRepository->put($debugUserAllTimeSetting);

        Notification::make()
            ->title('サーバー日時を設定しました')
            ->success()
            ->send();
    }

    public function resetTimeSetting(): void
    {
        // デバッグユーザー全体時間設定が未設定
        $debugUserAllTimeSettingRepository = app()->make(DebugUserAllTimeSettingRepository::class);
        $existsSetting = $debugUserAllTimeSettingRepository->exists();
        if (! $existsSetting) {
            Notification::make()
            ->title('サーバー日時の設定が未登録でした')
            ->success()
            ->send();

            return;
        }

        // デバッグユーザー全体時間設定を削除
        $debugUserAllTimeSettingRepository->delete();

        Notification::make()
            ->title('サーバー日時をリセットしました')
            ->success()
            ->send();
    }
}
