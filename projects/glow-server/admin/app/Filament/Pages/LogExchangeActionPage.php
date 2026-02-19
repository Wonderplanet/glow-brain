<?php

namespace App\Filament\Pages;

use App\Constants\LogTablePageConstants;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Log\LogExchangeAction;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\AthenaQueryTrait;
use App\Traits\RewardInfoGetTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogExchangeActionPage extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.log-exchange-action-page';
    public string $currentTab = UserSearchTabs::LOG_EXCHANGE->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = LogExchangeAction::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('交換日時')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_id')
                    ->label('交換所ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_exchange_lineup_id')
                    ->label('交換ラインナップID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trade_count')
                    ->label('交換回数')
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('cost_info')
                    ->label('コスト情報')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getCostsDtos());
                        }
                    ),
                RewardInfoColumn::make('reward_info')
                    ->label('報酬情報')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getRewardsDtos());
                        }
                    ),
                RewardInfoColumn::make('before_reward_info')
                    ->label('報酬情報(変換前)')
                    ->getStateUsing(
                        function ($record) {
                            return $this->getRewardInfos($record->getBeforeRewardsDtos());
                        }
                    ),
                TextColumn::make('nginx_request_id')
                    ->label('Nginx Request ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('request_id')
                    ->label('Request ID')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters([LogTablePageConstants::CREATED_AT_RANGE, LogTablePageConstants::NGINX_REQUEST_ID]),
                    [
                        Filter::make('mst_exchange_lineup_id')
                            ->form([
                                TextInput::make('mst_exchange_lineup_id')
                                    ->label('交換ラインナップID')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['mst_exchange_lineup_id'])) {
                                    return $query;
                                }
                                return $query->where('mst_exchange_lineup_id', 'like', "{$data['mst_exchange_lineup_id']}%");
                            }),
                    ]
                ),
                FiltersLayout::AboveContent
            )
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_exchange')
            ]);
    }
}
