<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseImportResource;

class EditMngMasterReleaseImport extends EditRecord
{
    protected static string $resource = MngMasterReleaseImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
