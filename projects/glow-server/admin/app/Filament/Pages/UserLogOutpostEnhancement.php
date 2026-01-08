<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Models\Log\LogOutpostEnhancement;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Tables\Columns\TextColumn;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;

class UserLogOutpostEnhancement extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-outpost-enhancement';
    public string $currentTab = UserSearchTabs::LOG_OUTPOST_ENHANCEMENT->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogOutpostEnhancement::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mst_outpost_enhancement_id')
                    ->label('ゲート強化情報')
                    ->getStateUsing(
                        function ($record) {
                            if ($record?->mst_outpost_enhancement) {
                                return '[' . $record->mst_outpost_enhancement?->id . '] ' . $record->mst_outpost_enhancement?->mst_outpost_enhancement_i18n?->name;
                            }
                            return;
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('before_level')
                    ->label('強化前レベル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('after_level')
                    ->label('強化後レベル')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('強化日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                $this->getCommonLogFilters(),
                FiltersLayout::AboveContent)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_outpost_enhancement')
            ]);
    }
}

