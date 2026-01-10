<?php

namespace App\Filament\Resources\MngContentCloseResource\Pages;

use App\Filament\Resources\MngContentCloseResource;
use App\Traits\MngCacheDeleteTrait;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMngContentClose extends CreateRecord
{
    use MngCacheDeleteTrait;

    protected static string $resource = MngContentCloseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $model = parent::handleRecordCreation($data);

        // キャッシュ削除
        $this->deleteMngContentCloseCache();

        return $model;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
