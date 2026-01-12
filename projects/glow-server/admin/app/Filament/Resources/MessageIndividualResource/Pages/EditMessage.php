<?php

namespace App\Filament\Resources\MessageIndividualResource\Pages;

use App\Filament\Resources\MessageIndividualResource;
use App\Filament\Traits\MessageEditTrait;
use App\Repositories\Adm\AdmMessageDistributionIndividualInputRepository;
use Filament\Resources\Pages\Page;

class EditMessage extends Page
{
    use MessageEditTrait;

    protected static string $resource = MessageIndividualResource::class;

    protected static string $view = 'filament.resources.message-resource.message';

    protected static ?string $title = '編集画面';
    
    public function mount(int|string $record): void
    {
        $this->repository = AdmMessageDistributionIndividualInputRepository::class;
        $this->isAlDistribution = false;
        $this->traitMount($record);
    }
}
