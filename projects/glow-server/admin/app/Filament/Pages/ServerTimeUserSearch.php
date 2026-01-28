<?php

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ServerTimeUserSearch extends Page
{
    use Authorizable;
    use WithPagination;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.server-time-user-search';
    protected static ?string $navigationGroup = NavigationGroups::QA_SUPPORT->value;
    protected static ?string $title = 'ユーザーサーバー時間変更';
    protected static ?string $slug = 'server-time-user-search';

    // 時間フォーマット
    private const DATETIME_FORMAT = 'Y年m月d日 H時i分s秒';
    // 入力タイムゾーン
    private const INPUT_TIMEZONE = 'Asia/Tokyo';

    // フォームで入力した値が以下のプロパティに保存される
    public ?string $userAllTime = null;

    public ?array $results = null;

    #[Url]
    public ?string $userId = null;

    #[Url]
    public ?string $myId = null;

    #[Url]
    public ?string $name = null;

    #[Url]
    public ?int $perPage = 10;

    public function Form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('userId')
                ->label('ユーザーID')
                ->string()
                ->id('search-user-id'),
            TextInput::make('myId')
                ->label('マイID')
                ->string()
                ->id('search-my-id'),
            TextInput::make('name')
                ->label('ユーザー名')
                ->string()
                ->id('search-name'),
        ]);
    }

    public function search(): void
    {
        $this->dispatch(
            'search',
            userId: $this->userId,
            myId: $this->myId,
            name: $this->name
        )->to('ServerTimeUserList');
    }
}
