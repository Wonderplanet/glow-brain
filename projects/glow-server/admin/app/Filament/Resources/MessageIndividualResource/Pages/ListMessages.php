<?php

namespace App\Filament\Resources\MessageIndividualResource\Pages;

use App\Filament\Resources\MessageIndividualResource;
use App\Filament\Traits\MessageListTrait;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    use MessageListTrait;

    protected static string $resource = MessageIndividualResource::class;
}
