<?php

namespace App\Filament\Pages;

use App\Filament\Authorizable;
use App\Services\SystemMaintenanceService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SystemMaintenanceImmediate extends Page
{
    use InteractsWithActions;
    use InteractsWithForms;
    use Authorizable;


    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.system-maintenance-immediate';

    protected ?string $heading = '全体メンテナンス 即時実行設定';

    protected static ?string $title = '全体メンテナンス 即時実行設定';

    protected static bool $shouldRegisterNavigation = false;


    public ?string $text = null;

    // フォームの設定
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('text')
                    ->label('メンテナンスメッセージ')
                    ->required()
                    ->default('ただいま全体メンテナンス中となります。'),
                Actions::make([
                    Action::make('cancel')
                        ->label('キャンセル')
                        ->color('gray')
                        ->url(SystemMaintenanceList::getUrl()),

                    Action::make('submit')
                        ->label('メンテナンス開始')
                        ->action(fn () => $this->startMaintenanceImmediately())
                        ->requiresConfirmation()
                        ->color('danger'),
                ])
            ]);
    }



    //　メンテナンスを即時に開始
    public function startMaintenanceImmediately(): void
    {
        /** @var SystemMaintenanceService $service */
        $service = app()->make(SystemMaintenanceService::class);
        $service->startMaintenanceImmediately(text: $this->text);

        redirect(SystemMaintenanceList::getUrl());
    }


    public function mount(): void
    {
        $this->form->fill();
    }

}
