<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;

class LogCurrencyRevertHistory extends Page
{
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?int $navigationSort = 103;

    protected static string $view = 'filament.pages.log-currency-revert-history';
    protected static ?string $title = '一次通貨返却履歴';

    /**
     * ユーザーIDの検索条件
     *
     * @var string
     */
    public string $userId = '';

    /**
     * 開始日の検索条件
     *
     * @var string
     */
    public string $startDate = '';

    /**
     * 終了日の検索条件
     *
     * @var string
     */
    public string $endDate = '';

    /**
     * 検索フォームの表示
     *
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form->schema([
            // ユーザーID検索
            TextInput::make('userId')
                ->label('ユーザーID検索')
                ->placeholder('ユーザーIDを入力')
                ->required()
                ->columnSpanFull(),
            // 検索期間
            Fieldset::make('検索期間')
                ->columns(2)
                ->schema([
                    // 開始日
                    DateTimePicker::make('startDate')
                        ->label('開始日')
                        ->placeholder('開始日を入力')
                        ->columnSpan(1),
                    // 終了日
                    DateTimePicker::make('endDate')
                        ->label('終了日')
                        ->placeholder('終了日を入力')
                        ->columnSpan(1),
                ])
                ->columnSpanFull(),

        ]);
    }

    /**
     * 検索条件を更新する
     *
     * @return void
     */
    public function updateSearch(): void
    {
        $this->dispatch(
            'searchUpdated',
            userId: $this->userId,
            startDate: $this->startDate,
            endDate: $this->endDate
        )
            ->to('LogCurrencyRevertHistoryList');
    }
}
