<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Models\Log\LogSuspectedUser;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\UserResourceLogCurrencyTrait;
use Filament\Tables\Filters\SelectFilter;
use App\Traits\UserLogTableFilterTrait;
use App\Constants\ContentType;
use App\Constants\CheatType;
use App\Tables\Columns\MstAdventBattleInfoColumn;
use Filament\Tables\Concerns\InteractsWithTable;

class UserLogSuspectedUser extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use UserLogTableFilterTrait;
    use UserResourceLogCurrencyTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-suspected-user';
    public string $currentTab = UserSearchTabs::LOG_SUSPECTED_USER->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogSuspectedUser::query()
            ->with([
                'mst_advent_battle'
            ])
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content_type')
                    ->label('コンテンツタイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return ContentType::tryFrom($record?->content_type)?->label() ?? '';
                    }),
                MstAdventBattleInfoColumn::make('target_id')
                    ->label('降臨バトル情報')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return $record->mst_advent_battle;
                        }
                    ),
                TextColumn::make('cheat_type')
                    ->label('不正タイプ')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return CheatType::tryFrom($record?->cheat_type)?->label() ?? '';
                    }),
                TextColumn::make('detail')
                    ->label('不正判定要因のデータ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('suspected_at')
                    ->label('不正疑い日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        SelectFilter::make('content_type')
                            ->options(ContentType::labels()->toArray())
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['value'])) {
                                    return $query;
                                }
                                return $query->where('content_type', $data);
                            })
                            ->label('コンテンツタイプ'),
                        Filter::make('target_id')
                            ->form([
                                TextInput::make('target_id')
                                    ->label('対象コンテンツID')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['target_id'])) {
                                    return $query;
                                }
                                return $query->where('target_id', $data['target_id']);
                            }),
                        SelectFilter::make('cheat_type')
                            ->options(CheatType::labels()->toArray())
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['value'])) {
                                    return $query;
                                }
                                return $query->where('cheat_type', $data);
                            })
                            ->label('不正タイプ'),
                    ]
                ),
                FiltersLayout::AboveContent)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('suspected_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_suspected_user')
            ]);
    }
}
