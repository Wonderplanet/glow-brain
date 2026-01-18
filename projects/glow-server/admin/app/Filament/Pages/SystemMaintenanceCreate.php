<?php

namespace App\Filament\Pages;

use App\Constants\SystemConstants;
use App\Filament\Authorizable;
use App\Services\SystemMaintenanceService;
use Carbon\CarbonImmutable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;

class SystemMaintenanceCreate extends Page implements HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithFormActions;
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.system-maintenance-create';

    protected ?string $heading = '全体メンテナンス 予約設定';

    protected static ?string $title = '全体メンテナンス 予約設定';

    protected static bool $shouldRegisterNavigation = false;


    public ?string $startAt = null;

    public ?string $endAt = null;

    public ?string $text = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    // フォームの設定
    public function form(Form $form): Form
    {
        $now = CarbonImmutable::now()->setSecond(0);

        return $form
            ->schema([
                DateTimePicker::make('startAt')
                    ->label('開始時刻 (JST)')
                    ->required()
                    ->before('endAt')
                    ->default($now)
                    ->seconds(false),
                DateTimePicker::make('endAt')
                    ->label('終了時刻 (JST)')
                    ->required()
                    ->after('startAt')
                    ->default($now)
                    ->seconds(false),
                Textarea::make('text')
                    ->label('メンテ文言')
                    ->required()
                    ->default('ただいま全体メンテナンス中となります。')
                    ->columnSpanFull(),
                Actions::make([
                    Action::make('cancel')
                        ->label('キャンセル')
                        ->color('gray')
                        ->url(SystemMaintenanceList::getUrl()),

                    Action::make('submit')
                        ->label('予約作成')
                        ->action(fn () => $this->create())
                        ->requiresConfirmation(),
                ])
            ])
            ->columns(2);
    }


    public function create(): void
    {
        // UTCで保存するので、JSTからUTCに変換
        $startAt = CarbonImmutable::parse(
            time: $this->startAt,
            timezone: SystemConstants::VIEW_TIMEZONE,
        )->setTimezone(SystemConstants::TIMEZONE_UTC);
        $endAt = CarbonImmutable::parse(
            time: $this->endAt,
            timezone: SystemConstants::VIEW_TIMEZONE,
        )->setTimezone(SystemConstants::TIMEZONE_UTC);

        /** @var SystemMaintenanceService $service */
        $service = app()->make(SystemMaintenanceService::class);
        $service->create(
            startAt: $startAt,
            endAt: $endAt,
            text: $this->text,
        );

        redirect(SystemMaintenanceList::getUrl());
    }
}
