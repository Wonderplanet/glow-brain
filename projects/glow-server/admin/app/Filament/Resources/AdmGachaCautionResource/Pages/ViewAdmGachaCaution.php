<?php

namespace App\Filament\Resources\AdmGachaCautionResource\Pages;

use App\Filament\Resources\AdmGachaCautionResource;
use App\Services\AdmGachaCautionService;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmGachaCaution extends ViewRecord
{
    protected static string $resource = AdmGachaCautionResource::class;

    protected static string $view = 'filament.pages.preview-html';

    public function getHtmlUrl(): string
    {
        /** @var \App\Models\Adm\AdmGachaCaution $admGachaCaution */
        $admGachaCaution = $this->record->getModel();

        /** @var AdmGachaCautionService $admGachaCautionService */
        $admGachaCautionService = app(AdmGachaCautionService::class);

        return $admGachaCautionService->makeHtmlUrl($admGachaCaution);
    }
}
