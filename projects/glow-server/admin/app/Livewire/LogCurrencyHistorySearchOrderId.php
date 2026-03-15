<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class LogCurrencyHistorySearchOrderId extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $orderId = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.log-currency-history-search-order-id');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
                    // 課金ID検索
                    TextInput::make('orderId')
                        ->label('課金ID検索')
                        ->placeholder('課金ID入力')
                        ->columnSpanFull(),
        ]);
    }

    public function search()
    {
        $this->dispatch('orderIdUpdated', orderId: $this->orderId)
            ->to('LogCurrencyHistoryList');
        $this->dispatch('orderIdUpdated')
            ->to('LogCurrencyHistoryUserTotal');
    }
}
