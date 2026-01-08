<?php

namespace App\Filament\Resources\DatalakeTransferResource\Pages;

use App\Filament\Resources\DatalakeTransferResource;
use App\Services\Datalake\DatalakeTransferService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDatalakeTransfers extends ListRecords
{
    protected static string $resource = DatalakeTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('transfer')
                ->label('データレイク転送手動実行')
                ->color('primary')
                ->action('executeTransfer')
                ->requiresConfirmation(),
        ];
    }

    public function executeTransfer(): void
    {
        $env = env('APP_ENV');
        $executionTime = now();
        $targetDate = $executionTime->subDay()->toImmutable();
        app(DatalakeTransferService::class)->execTransfer($env, $targetDate);

        Notification::make()
            ->title('データレイクへの転送を実行しました')
            ->success()
            ->send();
    }
}
