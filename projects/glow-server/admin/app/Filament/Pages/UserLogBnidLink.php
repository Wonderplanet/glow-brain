<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Models\Log\LogBnidLink;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UserLogBnidLink extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-bnid-link';
    public string $currentTab = UserSearchTabs::LOG_BNID_LINK->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogBnidLink::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action_type')
                    ->label('連携/解除')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('before_bn_user_id')
                    ->label('変更前のBNIDユーザーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('after_bn_user_id')
                    ->label('変更後のBNIDユーザーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('連携/解除日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        Filter::make('before_bn_user_id')
                            ->form([
                                TextInput::make('before_bn_user_id')
                                    ->label('変更前のBNIDユーザーID')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['before_bn_user_id'])) {
                                    return $query;
                                }
                                return $query->where('before_bn_user_id', 'like', "{$data['before_bn_user_id']}%");
                            }),
                        Filter::make('after_bn_user_id')
                            ->form([
                                TextInput::make('after_bn_user_id')
                                    ->label('変更後のBNIDユーザーID')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (blank($data['after_bn_user_id'])) {
                                    return $query;
                                }
                                return $query->where('after_bn_user_id', 'like', "{$data['after_bn_user_id']}%");
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
                    ->fileName('user_log_bnid_link')
            ]);
    }
}

