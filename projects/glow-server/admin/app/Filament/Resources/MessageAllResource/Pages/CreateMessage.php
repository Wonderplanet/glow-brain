<?php

namespace App\Filament\Resources\MessageAllResource\Pages;

use App\Filament\Resources\MessageAllResource;
use App\Filament\Traits\MessageCreateTrait;
use App\Repositories\Adm\AdmMessageDistributionInputRepository;
use Filament\Resources\Pages\Page;

/**
 * 全体メッセージ配布データ新規作成
 */
class CreateMessage extends Page
{
    use MessageCreateTrait;

    protected static string $resource = MessageAllResource::class;

    protected static string $view = 'filament.resources.message-resource.message';

    protected static ?string $title = '作成画面';

    public function mount(): void
    {
        $this->repository = AdmMessageDistributionInputRepository::class;
        $this->isAlDistribution = true;
        $this->traitMount();
    }
}
