<?php

namespace App\Filament\Resources\MngClientVersionResource\Pages;

use App\Filament\Resources\MngClientVersionResource;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\MngCacheDeleteTrait;
use Filament\Resources\Pages\EditRecord;

class EditMngClientVersion extends EditRecord
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $resource = MngClientVersionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // キャッシュ削除
        $this->deleteMngClientVersionCache();
    }
}
