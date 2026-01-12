<?php

namespace App\Filament\Resources\AdmGachaCautionResource\Pages;

use App\Filament\Resources\AdmGachaCautionResource;
use App\Models\Adm\AdmGachaCaution;
use App\Services\AdmGachaCautionService;
use Filament\Resources\Pages\EditRecord;

class EditAdmGachaCaution extends EditRecord
{
    protected static string $resource = AdmGachaCautionResource::class;

    protected function handleRecordUpdate($record, array $data): AdmGachaCaution
    {
        /** @var AdmGachaCautionService $admGachaCautionService */
        $admGachaCautionService = app(AdmGachaCautionService::class);

        /** @var AdmGachaCaution $beforeAdmGachaCaution */
        $beforeAdmGachaCaution = $record->getModel();
        return $admGachaCautionService->updateGachaCaution($beforeAdmGachaCaution, $data);
    }
}
