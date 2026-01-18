<?php

declare(strict_types=1);

namespace App\Filament\Resources\LogCurrencyRevertResource\Pages;

use App\Filament\Resources\LogCurrencyRevertResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class ListLogCurrencyRevert extends Page
{
    protected static string $resource = LogCurrencyRevertResource::class;

    protected static string $view = 'filament.pages.log-currency-revert';

    protected static ?string $title = '一次通貨返却';

    public string $userId = '';
    public string $startDate = '';
    public string $endDate = '';

    /**
     * GETパラメータで受け取る検索条件
     *
     * @var array
     */
    protected $queryString = [
        'userId',
        'startDate',
        'endDate',
    ];

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
            ->to('LogCurrencyRevert\LogCurrencyRevertList');
    }
}
