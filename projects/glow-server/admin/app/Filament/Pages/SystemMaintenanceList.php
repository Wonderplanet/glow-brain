<?php

namespace App\Filament\Pages;

use App\Constants\MaintenanceDisplayOrder;
use App\Constants\NavigationGroups;
use App\Constants\SystemConstants;
use App\Entities\MaintenanceEntity;
use App\Filament\Authorizable;
use App\Services\SystemMaintenanceService;
use Carbon\CarbonImmutable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;

class SystemMaintenanceList extends Page
{
    use InteractsWithActions;
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.system-maintenance-list';

    protected static ?string $navigationGroup = NavigationGroups::MAINTENANCE->value;

    protected static ?string $navigationLabel = '全体メンテナンス';

    protected static bool $shouldRegisterNavigation = true;

    protected ?string $heading = '全体メンテナンス';

    protected static ?string $title = '全体メンテナンス';

    protected static ?int $navigationSort = MaintenanceDisplayOrder::SYSTEM_MAINTENANCE_DISPLAY_ORDER->value; // メニューの並び順

    public ?bool $dataExist = false;

    protected array $data = [];

    // フォームを初期化
    public function mount(): void
    {
        /** @var SystemMaintenanceService $service */
        $service = app()->make(SystemMaintenanceService::class);
        $this->data = $service->getData();

        // データの有無をフラグで保持
        $this->dataExist = empty($this->data) ? false : true ;
    }

    public function getHeaderActions(): array
    {
        if ($this->dataExist) {
            return [];
        }

        return [
            \Filament\Actions\Action::make('即時メンテを設定')
                ->action(fn () => redirect(SystemMaintenanceImmediate::getUrl()))
                ->color('danger'),
            \Filament\Actions\Action::make('予約メンテを設定')
                ->action(fn () => redirect(SystemMaintenanceCreate::getUrl()))
                ->color('warning'),
        ];
    }

    protected function getViewData(): array
    {
        $now = CarbonImmutable::now();

        // テーブル表示用のデータ
        $tableRows = [];
        foreach ($this->data as $datum) {
            $maintenanceEntity = new MaintenanceEntity($datum);

            $tableRows[] = [
                'startAt' => $maintenanceEntity->getStartAtCarbon()
                    ->setTimezone(SystemConstants::VIEW_TIMEZONE)
                    ->format(SystemConstants::VIEW_DATETIME_FORMAT),
                'endAt' => $maintenanceEntity->getEndAtCarbon()
                    ->setTimezone(SystemConstants::VIEW_TIMEZONE)
                    ->format(SystemConstants::VIEW_DATETIME_FORMAT),
                'text' => $maintenanceEntity->getText(),
                'statusContent' => $maintenanceEntity->getStatusContent($now),
                'statusBadgeColor' => $maintenanceEntity->getStatusBadgeColor($now),
                'actions' => [
                    Action::make('operation')
                        ->label('操作')
                        ->button()
                        ->url(SystemMaintenanceEdit::getUrl(
                            parameters: ['sk' => $maintenanceEntity->getSk()],
                            isAbsolute: true
                        )),
                ],
            ];
        }

        return [
            'tableRows' => $tableRows,
        ];
    }

}
