<?php

namespace App\Filament\Resources\AdmInformationResource\Pages;

use App\Entities\TiptapContentEntity;
use App\Filament\Resources\AdmInformationResource;
use App\Models\Adm\AdmInformation;
use App\Services\AdmInformationService;
use App\Utils\StringUtil;
use Filament\Resources\Pages\EditRecord;

class EditAdmInformation extends EditRecord
{
    protected static string $resource = AdmInformationResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);
        $htmlImagePreviewPath = $admInformationService->getHtmlImagePreviewPath('');

        $tiptapContentEntity = new TiptapContentEntity($data['html_json'] ?? '');
        $data['html_json'] = $tiptapContentEntity->getImageSrcReplacedElements(
            function ($src) use ($htmlImagePreviewPath) {
                return StringUtil::joinPath(
                    $htmlImagePreviewPath,
                    basename($src),
                );
            }
        );
        return $data;
    }

    protected function handleRecordUpdate($record, array $data): AdmInformation
    {
        /** @var AdmInformationService $admInformationService */
        $admInformationService = app(AdmInformationService::class);

        /** @var AdmInformation $beforeAdmInformation */
        $beforeAdmInformation = $record->getModel();
        return $admInformationService->updateInformation($beforeAdmInformation, $data);
    }
}
