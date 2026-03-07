<?php

namespace App\Livewire;

use App\Models\Usr\UsrCurrencyPaid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class UsrCurrencyPaidList extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected $listeners = [
        'userIdUpdated' => 'onUserIdUpdated',
    ];

    public string $userId = '';

    public function table(Table $table): Table
    {
        return $table
            ->query(UsrCurrencyPaid::query()->where('usr_user_id', $this->userId))
            ->columns([
                Tables\Columns\TextColumn::make('seq_no')->label('購入順')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('os_platform')->label('OSPF')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('billing_platform')->label('課金PF')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('currency_code')->label('通貨コード')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('purchase_price')->label('価格')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('price_per_amount')->label('単価')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('purchase_amount')->label('購入個数')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('left_amount')->label('個数残高')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('receipt_unique_id')->label('receipt_unique_id')->alignCenter(),
                Tables\Columns\TextColumn::make('is_sandbox')->label('サンドボックス')
                    ->getStateUsing(function (UsrCurrencyPaid $row): string {
                        return $row->is_sandbox ? '◯' : '×';
                    })->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')->label('追加日')->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')->label('更新日')->alignCenter(),
                Tables\Columns\TextColumn::make('deleted_at')->label('削除日')->alignCenter(),
            ])
            ->defaultSort('seq_no')
            ->filters([
                Tables\Filters\SelectFilter::make('os_platform')
                    ->label('OSPF')
                    ->options(function (): array {
                        // os_platformに応じてフィルタオプションを生成
                        $UsrCurrencyPaidCollection = UsrCurrencyPaid::query()
                            ->where('usr_user_id', $this->userId)
                            ->get(['os_platform'])
                            ->groupBy('os_platform');
                        $options = [];
                        foreach ($UsrCurrencyPaidCollection->keys() as $pl) {
                            $options[$pl] = $pl;
                        }
                        return $options;
                    }),
                Tables\Filters\SelectFilter::make('billing_platform')
                    ->label('課金PF')
                    ->options(function ():array {
                        // billing_platformに応じてフィルタオプションを生成
                        $UsrCurrencyPaidCollection = UsrCurrencyPaid::query()
                            ->where('usr_user_id', $this->userId)
                            ->get(['billing_platform'])
                            ->groupBy('billing_platform');
                        $options = [];
                        foreach ($UsrCurrencyPaidCollection->keys() as $pl) {
                            $options[$pl] = $pl;
                        }
                        return $options;
                    }),
                Tables\Filters\Filter::make('currency_code')
                    ->form([
                        TextInput::make('currency_code')->label('通貨コード')
                    ])->query(function (Builder $query, array $data): Builder {
                        return $data['currency_code'] === null
                            ? $query
                            : $query->where('currency_code', 'LIKE', '%' . $data['currency_code'] . '%');
                    }),
                Tables\Filters\TernaryFilter::make('left_amount')
                    ->label('個数残高')
                    ->placeholder('0以外')
                    ->trueLabel('0のみ')
                    ->falseLabel('全件')
                    ->queries(
                        true: fn (Builder $query) => $query->where('left_amount', 0),
                        false: fn (Builder $query) => $query,
                        blank: fn (Builder $query) => $query->where('left_amount', '<>', 0),
                    ),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.usr-currency-paid-list');
    }

    public function onUserIdUpdated($userId): void
    {
        $this->userId = $userId;
        $table = $this->getTable();
        $this->table($table);
    }
}
