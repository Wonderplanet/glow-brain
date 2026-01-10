<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource;

class ListMngMasterReleaseImports extends ListRecords
{
    protected static string $resource = MngMasterReleaseImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
