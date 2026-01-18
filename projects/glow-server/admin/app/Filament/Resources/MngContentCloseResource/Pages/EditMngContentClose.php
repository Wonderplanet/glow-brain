<?php

namespace App\Filament\Resources\MngContentCloseResource\Pages;

use App\Filament\Resources\MngContentCloseResource;
use App\Traits\MngCacheDeleteTrait;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMngContentClose extends EditRecord
{
    use MngCacheDeleteTrait;

    protected static string $resource = MngContentCloseResource::class;

    public function getActions(): array
    {
        return [
            Action::make('return')
                ->label('戻る')
                ->url($this->getResource()::getUrl()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema(MngContentCloseResource::getFormSchema());
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $model = parent::handleRecordUpdate($record, $data);

        $this->deleteMngContentCloseCache();

        return $model;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
