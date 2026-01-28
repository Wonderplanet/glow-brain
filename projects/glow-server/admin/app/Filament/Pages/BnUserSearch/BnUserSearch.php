<?php

namespace App\Filament\Pages\BnUserSearch;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use LiveWire\Attributes\Url;
use Livewire\WithPagination;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Constants\OsType;
use App\Constants\BillingStatus;

class BnUserSearch extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.bn-user-search';

    protected static ?string $label = "検索";

    protected static ?string $title = '「ジャンプ+ ジャンブルラッシュ」管理ツール';

    public ?array $results = null;

    #[Url]
    public ?string $userId = null;
    public ?string $myId = null;
    public ?string $name = null;
    public ?string $nameMatch = null;
    public ?string $bnUserId = null;
    public ?string $osType = null;
    public ?string $minLevel = null;
    public ?string $maxLevel = null;
    public ?string $gameStartAtStart = null;
    public ?string $gameStartAtEnd = null;
    public ?string $lastLoginAtStart = null;
    public ?string $lastLoginAtEnd = null;
    public ?string $billingStatus = null;
    public ?int $perPage = 10;
    public ?string $entry = 'bnUserSearch';

    public function Form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('userId')
                ->label('プレイヤーID')
                ->string()
                ->id('search-user-id')
                ->inlineLabel(),
            TextInput::make('myId')
                ->label('マイID')
                ->string()
                ->id('search-my-id')
                ->inlineLabel(),
            Grid::make(2)
                ->schema([
                    TextInput::make('name')
                        ->label('プレイヤー名')
                        ->string()
                        ->id('search-name')
                        ->inlineLabel(),
                    Select::make('nameMatch')
                        ->options([
                            'partialMatch' => '部分一致',
                            'prefixMatch' => '前方一致',
                            'suffixMatch' => '後方一致',
                            'NoDistinction' => '全角半角/大文字小文字の区別なし',
                        ])
                        ->id('search-name-match')
                        ->label('検索条件')
                        ->inlineLabel(),
                ]),
            TextInput::make('bnUserId')
                ->label('引き継ぎID')
                ->string()
                ->id('search-bn-user-id')
                ->inlineLabel(),
            Select::make('osType')
                ->options(OsType::labels()->toArray())
                ->label('使用OS')
                ->inlineLabel(),
            Grid::make(2)
                ->schema([
                    TextInput::make('minLevel')
                        ->label('レベル')
                        ->string()
                        ->id('search-min-level')
                        ->numeric()
                        ->minValue(1)
                        ->inlineLabel(),
                    TextInput::make('maxLevel')
                        ->label('〜')
                        ->string()
                        ->id('search-max-level')
                        ->numeric()
                        ->minValue(1)
                        ->inlineLabel(),
                ]),
            Grid::make(2)
                ->schema([
                    DatePicker::make('gameStartAtStart')
                        ->label('アカウント作成日時')
                        ->string()
                        ->id('search-game-start-at-start')
                        ->inlineLabel(),
                    DatePicker::make('gameStartAtEnd')
                        ->label('〜')
                        ->string()
                        ->id('search-game-start-at-end')
                        ->inlineLabel(),
                ]),
            Grid::make(2)
                ->schema([
                    DatePicker::make('lastLoginAtStart')
                        ->label('最終ログイン日時')
                        ->string()
                        ->id('search-last-login-at-start')
                        ->inlineLabel(),
                    DatePicker::make('lastLoginAtEnd')
                        ->label('〜')
                        ->string()
                        ->id('search-last-login-at-end')
                        ->inlineLabel(),
                ]),
            Select::make('billingStatus')
                ->options(BillingStatus::labels()->toArray())
                ->label('課金有無')
                ->inlineLabel(),
        ]);
    }

    public function search(): void
    {
        $this->dispatch(
            'search',
            userId: $this->userId,
            myId: $this->myId,
            name: $this->name,
            nameMatch: $this->nameMatch,
            bnUserId: $this->bnUserId,
            osType: $this->osType,
            level: [
                'minLevel' => $this->minLevel,
                'maxLevel' => $this->maxLevel,
            ],
            gameStartAt: [
                'gameStartAtStart' => $this->gameStartAtStart,
                'gameStartAtEnd' => $this->gameStartAtEnd,
            ],
            lastLoginAt: [
                'lastLoginAtStart' => $this->lastLoginAtStart,
                'lastLoginAtEnd' => $this->lastLoginAtEnd,
            ],
            billingStatus: $this->billingStatus,
            entry: $this->entry,

        )->to('UserSearchList');
    }
}
