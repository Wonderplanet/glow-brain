<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Traits\LogTriggerInfoGetTrait;
use App\Models\Log\LogExp;
use App\Traits\UserResourceLogTrait;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class UserLogExp extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use AthenaQueryTrait;
    use LogTriggerInfoGetTrait;
    use UserResourceLogTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-exp';
    public string $currentTab = UserSearchTabs::LOG_EXP->value;

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
        $query = LogExp::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->columns(
                self::getResourceLogColumns()
            )
            ->filters(
                self::getResourceLogFilters()
                , FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_exp')
            ]);
    }
}

