<?php

namespace App\Filament\Resources\MessageAllResource\Pages;

use App\Filament\Resources\MessageAllResource;
use App\Filament\Traits\MessageListTrait;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    use MessageListTrait;

    protected static string $resource = MessageAllResource::class;
}
