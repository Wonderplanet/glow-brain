<?php

namespace App\Filament\Resources\AdmInformationResource\Pages;

use App\Filament\Resources\AdmInformationResource;
use App\Services\AdmInformationService;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmInformation extends ViewRecord
{
    protected static string $resource = AdmInformationResource::class;

    protected static string $view = 'filament.pages.preview-html';

    public function getHtmlUrl(): string
    {
        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);

        return $admInformationService->getHtmlUrl($this->record->id);
    }
}
