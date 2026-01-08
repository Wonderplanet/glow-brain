<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class UsrUserCurrencyHeld extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.usr-user-currency-held';

    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?string $title = 'ユーザー所持通貨内訳表示';

    public string $userId = '';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('userId')
                ->label(new HtmlString("<p class='text-2xl'>ユーザー検索</p>"))
                ->placeholder('ユーザーIDを入力')
                ->columnSpanFull(),
        ]);
    }

    public function updateUserId(): void
    {
        $this->dispatch('userIdUpdated', userId: $this->userId)
            ->to('UsrCurrencyPaidList');
        $this->dispatch('userIdUpdated', userId: $this->userId)
            ->to('UsrUserCurrencyHeldOverView');
    }
}
