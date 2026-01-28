<?php

namespace App\Filament\Resources\CollectCurrencyFreeHistoryResource\Pages;

use App\Filament\Resources\CollectCurrencyFreeHistoryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectCurrencyFreeHistory extends ViewRecord
{
    protected static string $resource = CollectCurrencyFreeHistoryResource::class;

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
            ->url(fn (): string => route('filament.admin.resources.collect-currency-free-histories.index'));
    }
}
