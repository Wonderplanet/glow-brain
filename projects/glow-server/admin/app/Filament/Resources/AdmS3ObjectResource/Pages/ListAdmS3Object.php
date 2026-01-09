<?php

namespace App\Filament\Resources\AdmS3ObjectResource\Pages;

use App\Filament\Resources\AdmS3ObjectResource;
use App\Facades\Promotion;
use App\Services\AdmS3ObjectService;
use Filament\Resources\Pages\ListRecords;

class ListAdmS3Object extends ListRecords
{
    protected static string $resource = AdmS3ObjectResource::class;

    protected function getHeaderActions(): array
    {
        if (Promotion::isPromotionDestinationEnvironment()) {
            return [];
        }

        $admS3ObjectService = app(AdmS3ObjectService::class);

        return [
            $admS3ObjectService->getAddS3BucketScopeAction(),

            $admS3ObjectService->getUploadAssetsAction(),

            $admS3ObjectService->getSyncAdmS3ObjectFromS3Action(),
        ];
    }
}
