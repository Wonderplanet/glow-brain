<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Models\Log\LogLogin;
use App\Constants\UserSearchTabs;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Constants\LogLoginType;

class UserLogLogin extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-login';
    public string $currentTab = UserSearchTabs::LOG_LOGIN->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogLogin::query()
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
                TextColumn::make('login_count')
                    ->label('ログイン回数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_day_first_login')
                    ->label('ログイン識別')
                    ->getStateUsing(
                        function ($record) {
                            $isDayFirstLogin = LogLoginType::tryFrom($record->is_day_first_login);
                            return $isDayFirstLogin->label();
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('login_day_count')
                    ->label('ログイン日数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('login_continue_day_count')
                    ->label('連続ログイン日数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('comeback_day_count')
                    ->label('最終ログインから復帰にかかった日数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('ログイン日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        SelectFilter::make('is_day_first_login')
                            ->label('ログイン識別')
                            ->options(LogLoginType::labels()),
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
                    ->fileName('user_log_login')
            ]);
    }
}

