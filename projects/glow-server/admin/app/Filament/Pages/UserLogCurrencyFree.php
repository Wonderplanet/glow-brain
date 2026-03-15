<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Models\Log\LogCurrencyFree;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\UserResourceLogCurrencyTrait;
use Filament\Tables\Concerns\InteractsWithTable;

class UserLogCurrencyFree extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use UserResourceLogCurrencyTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-currency-free';
    public string $currentTab = UserSearchTabs::LOG_CURRENCY_FREE->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogCurrencyFree::query()
            ->where('usr_user_id', $this->userId);

        $baseColumns = [
            TextColumn::make('logging_no')
                ->label('ログ登録番号')
                ->searchable()
                ->sortable(),
            TextColumn::make('os_platform')
                ->label('OS')
                ->searchable()
                ->sortable(),
            // 無償購入前の残高
            TextColumn::make('before_ingame_amount')
                ->label('購入前の残高')
                ->searchable()
                ->sortable(),
            // 無償購入後の残高
            TextColumn::make('current_ingame_amount')
                ->label('購入後の残高')
                ->getStateUsing(function ($record): string {
                    // 現在の残高と増減分を表示
                    $result = $record->current_ingame_amount .
                        ' (' .
                        ($record->change_ingame_amount > 0 ? '+' : '') .
                        $record->change_ingame_amount .
                        ')';
                    return $result;
                }),
        ];

        $addColumns = $this->getResourceLogCurrencyColumns();

        $columns = array_merge($baseColumns, $addColumns);

        $baseFilters = [
            Filter::make('logging_no')
                ->form([
                    TextInput::make('logging_no')
                        ->label('ログ登録番号')
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['logging_no'])) {
                        return $query;
                    }
                    return $query->where('logging_no', 'like', "{$data['logging_no']}%");
                }),
        ];
        $addFilters = $this->getResourceLogCurrencyFilters();

        $filters = array_merge($baseFilters, $addFilters);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns(
                $columns
            )
            ->filters(
                $filters
                , FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersFormColumns(3)
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_currency_free')
            ]);
    }
}
