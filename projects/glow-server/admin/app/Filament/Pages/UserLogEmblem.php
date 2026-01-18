<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Traits\LogTriggerInfoGetTrait;
use App\Models\Log\LogEmblem;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Tables\Columns\MstEmblemInfoColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use App\Tables\Columns\LogTriggerValueColumn;
use App\Constants\LogResourceTriggerSource;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class UserLogEmblem extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use LogTriggerInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-emblem';
    public string $currentTab = UserSearchTabs::LOG_EMBLEM->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function getTableRecords(): Paginator | CursorPaginator
    {
        // AthenaQueryTraitの処理を実行（Athena使用時はpaginatorの中身が置き換わる）
        $paginator = $this->getTableRecordsWithAthena();

        // LogTrigger情報を追加
        $this->addLogTriggerInfoToPaginatedRecords($paginator);

        return $paginator;
    }

    private function table(Table $table)
    {
        $query = LogEmblem::query()
            ->with([
                'mst_emblem'
            ])
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                MstEmblemInfoColumn::make('emblem')
                    ->label('エンブレム情報')
                    ->getStateUsing(function ($record){
                        if ($record) {
                            return $record->mst_emblem;
                        }
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('変動量')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trigger_source')
                    ->label('経緯情報ソース')
                    ->getStateUsing(
                        function ($record) {
                            if ($record?->trigger_source !== null) {
                                $triggerSource = LogResourceTriggerSource::tryFrom($record?->trigger_source);
                                if ($triggerSource !== null) {
                                    return $triggerSource->label();
                                }
                            }
                            return $record->trigger_source;
                        }
                    )
                    ->searchable()
                    ->sortable(),
                LogTriggerValueColumn::make('trigger_value')
                    ->label('経緯情報値')
                    ->getStateUsing(
                        function ($record) {
                            return $record->log_trigger_info ?? null;
                        })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trigger_option')
                    ->label('経緯情報オプション')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('獲得/消費日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        Filter::make('mst_emblem_id')
                            ->form([
                                TextInput::make('mst_emblem_id')
                                    ->label('エンブレム情報')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['mst_emblem_id'])) {
                                    return $query;
                                }
                                return $query->where('mst_emblem_id', 'like', "{$data['mst_emblem_id']}%");
                            }),
                    ]
                )
            , FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_emblem')
            ]);
    }
}

