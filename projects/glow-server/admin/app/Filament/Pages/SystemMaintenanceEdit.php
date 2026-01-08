<?php

namespace App\Filament\Pages;

use App\Constants\SystemConstants;
use App\Entities\MaintenanceEntity;
use App\Filament\Authorizable;
use App\Services\SystemMaintenanceService;
use App\Traits\NotificationTrait;
use Carbon\CarbonImmutable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class SystemMaintenanceEdit extends Page
{
    use InteractsWithActions;
    use InteractsWithInfolists;
    use Authorizable;
    use NotificationTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.system-maintenance-edit';

    protected ?string $heading = '全体メンテナンス 設定編集';

    protected static ?string $title = '全体メンテナンス 設定編集';

    protected static bool $shouldRegisterNavigation = false;


    #[Url]
    public ?string $sk = null;

    protected ?MaintenanceEntity $maintenanceEntity = null;

    protected SystemMaintenanceService $systemMaintenanceService;

    // フォームを初期化
    public function mount(): void
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        $this->systemMaintenanceService = app(SystemMaintenanceService::class);

        $data = $this->systemMaintenanceService->getDataByKey($this->sk);

        $this->maintenanceEntity = new MaintenanceEntity($data);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        if (is_null($this->maintenanceEntity)) {
            $this->initialize();
        }

        $now = CarbonImmutable::now();
        $isUnderMaintenance = $this->maintenanceEntity->isUnderMaintenance($now);

        $statusBadgeColor = $this->maintenanceEntity->getStatusBadgeColor($now);

        return $infolist
            ->state([
                'startAt' => $this->maintenanceEntity->getStartAtCarbon(),
                'endAt' => $this->maintenanceEntity->getEndAtCarbon(),
                'text' => $this->maintenanceEntity->getText(),
                'status' => $this->maintenanceEntity->getStatusContent($now),
            ])
            ->schema([
                Fieldset::make('メンテナンス設定')
                    ->schema([
                        TextEntry::make('status')
                            ->label('ステータス')
                            ->columnSpan(1)
                            ->badge()
                            ->color(function () use ($statusBadgeColor) {
                                return $statusBadgeColor;
                            }),

                        TextEntry::make('startAt')
                            ->label('開始日時 (JST)')
                            ->dateTime(
                                format: SystemConstants::VIEW_DATETIME_FORMAT,
                                timezone: SystemConstants::VIEW_TIMEZONE,
                            )
                            ->columnSpan(1),
                        TextEntry::make('endAt')
                            ->label('終了日時 (JST)')
                            ->dateTime(
                                format: SystemConstants::VIEW_DATETIME_FORMAT,
                                timezone: SystemConstants::VIEW_TIMEZONE,
                            )
                            ->columnSpan(1),

                        TextEntry::make('text')
                            ->label('メンテ文言')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Actions::make([
                    Action::make('delete')
                        ->label('削除')
                        ->requiresConfirmation()
                        ->visible($isUnderMaintenance === false)
                        ->disabled($isUnderMaintenance)
                        ->color(function () use ($isUnderMaintenance) {
                            return $isUnderMaintenance ? 'gray' : 'danger';
                        })
                        ->action(fn () => $this->delete()),

                    Action::make("unavailable")
                        ->label('無効化')
                        ->requiresConfirmation()
                        ->visible($this->maintenanceEntity->isEnable() && $isUnderMaintenance === false)
                        ->action(fn () => $this->unavailable()),

                    Action::make("available")
                        ->label('有効化')
                        ->requiresConfirmation()
                        ->visible($this->maintenanceEntity->isDisable())
                        ->action(fn () => $this->available()),

                    Action::make('updateMaintenance')
                        ->label('変更')
                        ->requiresConfirmation()
                        ->form([
                            DateTimePicker::make('startAt')
                                ->label('開始日時（JST）')
                                ->default($this->maintenanceEntity->getStartAtCarbon())
                                ->timezone(SystemConstants::FORM_INPUT_TIMEZONE)
                                ->required()
                                ->seconds(false)
                                ->columns(1),
                            DateTimePicker::make('endAt')
                                ->label('終了日時（JST）')
                                ->default($this->maintenanceEntity->getEndAtCarbon())
                                ->timezone(SystemConstants::FORM_INPUT_TIMEZONE)
                                ->required()
                                ->seconds(false)
                                ->columns(1),
                            Textarea::make('text')
                                ->label('メンテ文言')
                                ->required()
                                ->default($this->maintenanceEntity->getText())
                                ->columnSpanFull(),
                        ])
                        ->action(fn (array $data) => $this->update(
                            startAt: $data['startAt'],
                            endAt: $data['endAt'],
                            text: $data['text']
                        )),

                    Action::make('stopMaintenanceImmediately')
                        ->label('メンテナンス即時停止')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible($isUnderMaintenance)
                        ->action(fn () => $this->stopMaintenanceImmediately())
                ])->alignCenter(),

                Actions::make([
                    Action::make('back')
                        ->label('一覧に戻る')
                        ->color('gray')
                        ->url(SystemMaintenanceList::getUrl()),
                ])->alignLeft(),
            ]);
    }

    // メンテナンス設定データを削除
    protected function delete(): void
    {
        $this->systemMaintenanceService->delete(
            SK: strval($this->sk)
        );

        // EventBridge Scheduler の設定を無効にする
        $this->systemMaintenanceService->disableSchedule();

        $this->sendProcessCompletedNotification(
            title: 'メンテナンスデータ削除完了',
            body: '',
        );

        redirect(SystemMaintenanceList::getUrl());
    }


    // 有効フラグを立てる
    protected function available(): void
    {
        $this->systemMaintenanceService->available(
            SK: strval($this->sk)
        );

        // EventBridge Scheduler の設定を有効にする
        $this->systemMaintenanceService->enableSchedule(
            start: $this->maintenanceEntity->getStartAtCarbon(),
            end: $this->maintenanceEntity->getEndAtCarbon(),
        );

        $this->sendProcessCompletedNotification(
            title: 'メンテナンスデータ有効化完了',
            body: '',
        );
    }


    // 有効フラグを落とす
    protected function unavailable(): void
    {
        $this->systemMaintenanceService->unavailable(
            SK: strval($this->sk)
        );

        // EventBridge Scheduler の設定を無効にする
        $this->systemMaintenanceService->disableSchedule();

        $this->sendProcessCompletedNotification(
            title: 'メンテナンスデータ無効化完了',
            body: '',
        );
    }


    // 終了時刻の変更
    protected function update(string $startAt, string $endAt, string $text): void
    {
        $now = CarbonImmutable::now(timezone: SystemConstants::TIMEZONE_UTC);

        // JSTでフォーム入力されるので、UTCに変換して保存する
        $startAt = CarbonImmutable::parse($startAt, SystemConstants::FORM_INPUT_TIMEZONE)
            ->setTimezone(SystemConstants::TIMEZONE_UTC);
        $endAt = CarbonImmutable::parse($endAt, SystemConstants::FORM_INPUT_TIMEZONE)
            ->setTimezone(SystemConstants::TIMEZONE_UTC);

        // 終了時刻 > 開始時刻となるようにする
        if ($endAt <= $startAt) {
            $this->sendDangerNotification(
                title: 'メンテナンスデータ更新時のエラー',
                body: '終了時刻は開始時刻より早い時間に設定できません',
            );
            return;
        }

        $this->systemMaintenanceService->updatePeriodAndText(
            SK: $this->sk,
            startAt: $startAt,
            endAt: $endAt,
            text: $text,
        );

        // 「有効」状態に設定されているのであればスケジューラーを設定しなおす
        if ($this->maintenanceEntity->isEnable()) {
            $this->systemMaintenanceService->enableSchedule(
                start: $startAt,
                end: $endAt,
            );

            // 現在実行中であれば Lambda を実行する（そうでないと固定レスポンスが修正されない）
            if ($now->between($startAt, $endAt)) {
                $this->systemMaintenanceService->invokeLambdaFunction();
            }
        }

        $this->sendProcessCompletedNotification(
            title: 'メンテナンスデータ更新完了',
            body: '',
        );
    }


    // メンテナンスの即時停止
    private function stopMaintenanceImmediately(): void
    {
        $this->systemMaintenanceService->stopMaintenanceImmediately($this->sk);

        $this->sendProcessCompletedNotification(
            title: 'メンテナンス即時停止完了',
            body: '',
        );
    }
}
