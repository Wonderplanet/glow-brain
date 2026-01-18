<?php

namespace App\Filament\Pages\SuspendedUser;

use App\Constants\NavigationGroups;
use App\Constants\UserStatus;
use App\Filament\Authorizable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use LiveWire\Attributes\Url;
use Livewire\WithPagination;

class SuspendedUserSearch extends Page
{
    use Authorizable;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = NavigationGroups::BAN->value;

    protected static string $view = 'filament.pages.suspended-user-search';

    protected static ?string $label = "検索";

    protected static ?string $title = 'アカウント停止';

    public ?array $results = null;

    #[Url]
    public ?string $userId = null;

    #[Url]
    public ?string $myId = null;

    #[Url]
    public ?string $name = null;

    #[Url]
    public ?int $status = null;

    #[Url]
    public ?int $perPage = 10;

    public function Form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('userId')
                ->label('プレイヤーID')
                ->string()
                ->id('search-user-id'),
            TextInput::make('myId')
                ->label('マイID')
                ->string()
                ->id('search-my-id'),
            TextInput::make('name')
                ->label('名前')
                ->string()
                ->id('search-name'),
            Select::make('status')
                ->label('ステータス')
                ->options(UserStatus::labels()->toArray())
                ->id('search-status'),
        ]);
    }

    public function search(): void
    {
        $this->dispatch(
            'search',
            userId: $this->userId,
            myId: $this->myId,
            name: $this->name,
            status: $this->status
        )->to('SuspendedUserList');
    }
}
