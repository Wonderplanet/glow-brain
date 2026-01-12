<?php

namespace App\Filament\Resources\AdmGachaCautionResource\Pages;

use App\Filament\Resources\AdmGachaCautionResource;
use App\Services\AdmGachaCautionService;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdmGachaCaution extends CreateRecord
{
    protected static string $resource = AdmGachaCautionResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')->label('作成')
                ->action('create'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param array<mixed> $data フォームデータ
     * @return \App\Models\Adm\AdmGachaCaution
     */
    public function handleRecordCreation(array $data): Model
    {
        /** @var AdmGachaCautionService $admGachaCautionService */
        $admGachaCautionService = app(AdmGachaCautionService::class);

        return $admGachaCautionService->createGachaCaution($data);
    }
}
