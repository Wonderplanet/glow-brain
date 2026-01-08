<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class LogCurrencyHistorySearch extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $userId = '';
    public ?string $userName = '';
    public ?string $triggerId = '';
    public ?string $triggerName = '';
    public ?string $startDate = '';
    public ?string $endDate = '';
    public ?string $minVipPoint = '';
    public ?string $maxVipPoint = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.log-currency-history-search');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            // ユーザー情報検索
            TextInput::make('userId')
                ->label('ユーザーID')
                ->placeholder('ユーザーID入力'),
            TextInput::make('userName')
                ->label('ユーザー名')
                ->placeholder('ユーザー名入力'),
            TextInput::make('triggerId')
                ->label('購入/消費した商品ID')
                ->placeholder('購入/消費した商品ID'),
            TextInput::make('triggerName')
                ->label('購入/消費した商品名')
                ->placeholder('購入/消費した商品名'),
            Fieldset::make('購入/消費期間')
                ->schema([
                    DateTimePicker::make('startDate')
                        ->label('開始日時')
                        ->placeholder('開始日時'),
                    DateTimePicker::make('endDate')
                        ->label('終了日時')
                        ->placeholder('終了日時'),
                ]),
            Fieldset::make('VIPポイント')
                ->schema([
                    TextInput::make('minVipPoint')
                        ->label('最小VIPポイント')
                        ->placeholder('最小VIPポイント入力'),
                    TextInput::make('maxVipPoint')
                        ->label('最大VIPポイント')
                        ->placeholder('最大VIPポイント入力'),
                ]),
        ])
            ->columns(4);
    }

    public function search(): void
    {
        $this->dispatch(
            'searchUpdated',
            userId: $this->userId,
            userName: $this->userName,
            triggerId: $this->triggerId,
            triggerName: $this->triggerName,
            startDate: $this->startDate,
            endDate: $this->endDate,
            minVipPoint: $this->minVipPoint,
            maxVipPoint: $this->maxVipPoint,
        )
            ->to('LogCurrencyHistoryList');

        // ユーザー情報の表示
        $this->dispatch(
            'userIdUpdated',
            userId: $this->userId,
        )
            ->to('LogCurrencyHistoryUserTotal');
    }
}
