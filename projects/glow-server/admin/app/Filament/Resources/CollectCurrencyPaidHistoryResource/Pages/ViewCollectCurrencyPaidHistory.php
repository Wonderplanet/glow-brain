<?php

namespace App\Filament\Resources\CollectCurrencyPaidHistoryResource\Pages;

use App\Filament\Resources\CollectCurrencyPaidHistoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectCurrencyPaidHistory extends ViewRecord
{
    protected static string $resource = CollectCurrencyPaidHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->backButton(),
        ];
    }

    /**
     * 一覧に戻す
     *
     * @return Action
     */
    public function backButton(): Action
    {
        return Action::make('backButton')
            ->label('戻る')
            ->url(self::getResource()::getUrl('index'));
    }
}
