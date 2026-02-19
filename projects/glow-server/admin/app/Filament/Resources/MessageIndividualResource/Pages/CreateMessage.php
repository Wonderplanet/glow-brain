<?php

namespace App\Filament\Resources\MessageIndividualResource\Pages;

use App\Filament\Resources\MessageIndividualResource;
use App\Filament\Traits\MessageCreateTrait;
use App\Repositories\Adm\AdmMessageDistributionIndividualInputRepository;
use Filament\Resources\Pages\Page;

/**
 * 個別メッセージ配布データ新規作成
 */
class CreateMessage extends Page
{
    use MessageCreateTrait;

    protected static string $resource = MessageIndividualResource::class;

    protected static string $view = 'filament.resources.message-resource.message';

    protected static ?string $title = '作成画面';

    public function mount(): void
    {
        $this->repository = AdmMessageDistributionIndividualInputRepository::class;
        $this->isAlDistribution = false;
        $this->traitMount();
    }
}
