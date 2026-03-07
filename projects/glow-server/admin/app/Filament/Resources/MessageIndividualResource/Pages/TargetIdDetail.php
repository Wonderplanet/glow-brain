<?php

namespace App\Filament\Resources\MessageIndividualResource\Pages;

use App\Filament\Resources\MessageIndividualResource;
use App\Filament\Traits\MessageTargetIdDetailTrait;
use App\Repositories\Adm\AdmMessageDistributionIndividualInputRepository;
use Filament\Resources\Pages\Page;

class TargetIdDetail extends Page
{
    use MessageTargetIdDetailTrait;

    protected static string $resource = MessageIndividualResource::class;

    protected static string $view = 'filament.resources.message-resource.pages.target-id-detail';

    protected static ?string $title = 'メッセージ配布対象ID一覧画面';

    public function mount(int|string $record): void
    {
        $this->repository = AdmMessageDistributionIndividualInputRepository::class;
        $this->traitMount($record);
    }
}
