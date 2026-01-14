<?php

namespace App\Filament\Resources\MessageAllResource\Pages;

use App\Filament\Resources\MessageAllResource;
use App\Filament\Traits\MessageEditTrait;
use App\Repositories\Adm\AdmMessageDistributionInputRepository;
use Filament\Resources\Pages\Page;

class EditMessage extends Page
{
    use MessageEditTrait;

    protected static string $resource = MessageAllResource::class;

    protected static string $view = 'filament.resources.message-resource.message';

    protected static ?string $title = '編集画面';

    public function mount(int|string $record): void
    {
        $this->repository = AdmMessageDistributionInputRepository::class;
        $this->isAlDistribution = true;
        $this->traitMount($record);
    }
}
