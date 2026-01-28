<?php

namespace App\Filament\Resources\IgnSettingResource\Pages;

use App\Filament\Resources\IgnSettingResource;
use App\Models\Mng\MngInGameNotice;
use App\Services\IgnService;
use App\Traits\DatabaseTransactionTrait;
use App\Traits\MngCacheDeleteTrait;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateIgnSetting extends CreateRecord
{
    use DatabaseTransactionTrait;
    use MngCacheDeleteTrait;

    protected static string $resource = IgnSettingResource::class;
    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('作成')
            ->requiresConfirmation()
            ->action(fn () => $this->create())
            ->modalHeading('注意')
            ->modalDescription('IGN作成を完了しますか？')
            ->modalCancelActionLabel('続ける')
            ->modalSubmitActionLabel('完了')
            ->modalIcon('heroicon-o-exclamation-triangle');
    }

    protected function handleRecordCreation(array $data): MngInGameNotice
    {
        /** @var IgnService $ignService */
        $ignService = app(IgnService::class);

        $mngInGameNotice = $this->transaction(function () use ($ignService, $data) {
            return $ignService->createIgn($data);
        });

        // キャッシュ削除
        $this->deleteMngInGameNoticeCache();

        return $mngInGameNotice;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
