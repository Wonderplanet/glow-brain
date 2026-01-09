<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Traits\LogTriggerInfoGetTrait;
use App\Models\Log\LogItem;
use App\Traits\UserResourceLogTrait;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Tables\Columns\MstIdColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Pages\MstItemDetail;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class UserLogItem extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use LogTriggerInfoGetTrait;
    use UserResourceLogTrait;
    use AthenaQueryTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-item';
    public string $currentTab = UserSearchTabs::LOG_ITEM->value;

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
        $query = LogItem::query()
            ->where('usr_user_id', $this->userId);

        $columns = self::getResourceLogColumns();
        $addColumn = ['mst_item_info' =>
            MstIdColumn::make('mst_item_info')
                ->label('アイテム情報')
                ->getMstUsing(
                    function (LogItem $logItem) {
                        return $logItem->mst_item;
                    }
                )
                ->getMstDetailPageUrlUsing(
                    function (LogItem $logItem) {
                        return MstItemDetail::getUrl(
                            [
                                'mstItemId' => $logItem->mst_item_id,
                            ]
                        );
                    }
                )
        ];
        $columns = array_slice($columns, 0, 2, true) + $addColumn + array_slice($columns, 2, null, true);

        $filters = array_merge(
            self::getResourceLogFilters(),
            [
                Filter::make('mst_item_id')
                    ->form([
                        TextInput::make('mst_item_id')
                            ->label('アイテム情報')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mst_item_id'])) {
                            return $query;
                        }
                        return $query->where('mst_item_id', 'like', "{$data['mst_item_id']}%");
                    }),
            ]
        );

        return $table
            ->query($query)
            ->columns(
                $columns
            )
            ->filters(
                $filters, FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_item')
            ]);
    }
}

