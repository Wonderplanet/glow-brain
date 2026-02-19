<?php

namespace App\Filament\Pages;

use App\Constants\ContentType;
use App\Constants\UserStatus;
use App\Filament\Authorizable;
use App\Filament\Pages\SuspendedUser\SuspendedUserSearch;
use App\Models\Adm\AdmUserBanOperateHistory;
use App\Models\Log\LogSuspectedUser;
use App\Models\Usr\UsrUser;
use Filament\Actions\Action as FilamentAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuspendedUser extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.suspended-user';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'アカウント停止詳細';

    public string $userId = '';
    public int $status = 0;
    public bool $perpetuallyStopped = false;
    public string $message = '';
    public string $messageBackgroundColor = '';

    protected $queryString = [
        'userId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            SuspendedUserSearch::getUrl() => 'アカウント停止',
            self::getUrl(['userId' => $this->userId]) => 'アカウント停止詳細',
        ]);

        $usrUser = UsrUser::query()
            ->where('id', $this->userId)
            ->first();

        switch ($usrUser->status) {
            case UserStatus::BAN_PERMANENT->value:
                $this->status = UserStatus::BAN_PERMANENT->value;
                $this->message = 'このアカウントは永久停止されています。';
                $this->messageBackgroundColor = UserStatus::BAN_PERMANENT->color(true);
                break;
            case UserStatus::BAN_TEMPORARY_CHEATING->value:
            case UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value:
                $this->status = UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value;
                $this->message = 'このアカウントは一時停止中です。';
                $this->messageBackgroundColor = UserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->color(true);
                break;
            case UserStatus::REFUNDING->value:
                $this->status = UserStatus::REFUNDING->value;
                $this->message = 'このアカウントは返金対応中です。';
                $this->messageBackgroundColor = UserStatus::REFUNDING->color(true);
                break;
        }
    }

    public function userInfoList(): Infolist
    {
        $usrUser = UsrUser::query()
            ->where('id', $this->userId)
            ->with([
                'usr_user_profiles'
            ])
            ->first();

        $usrUserProfile = $usrUser->usr_user_profiles;

        $state = [
            'id' => $usrUser->id,
            'my_id' => $usrUserProfile->my_id,
            'name' => $usrUserProfile->name,
            'status' => $usrUser->getUserStatus(),
        ];
        $fieldset = Fieldset::make('ユーザー詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('my_id')->label('MY_ID'),
                TextEntry::make('name')->label('名前'),
                TextEntry::make('status')->label('ステータス'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function banTemporaryInfoList(): Infolist
    {
        $admUserBanOperateHistory = AdmUserBanOperateHistory::getBanTemporariesByUsrUserId(
            $this->userId,
            'temporary_total_count'
        );

        $usrUser = UsrUser::query()
            ->where('id', $this->userId)
            ->first();

        $state = [
            'temporary_total_count' => $admUserBanOperateHistory->temporary_total_count,
            'suspend_end_at' => $usrUser->suspend_end_at,
        ];

        $fieldset = Fieldset::make('アカウント一時停止詳細')
            ->schema([
                Grid::make(1)->schema([
                    TextEntry::make('temporary_total_count')->label('アカウント一時停止回数')
                ]),
                TextEntry::make('suspend_end_at')
                    ->label('アカウント一時停止解除予定日時'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function table(Table $table): Table
    {

        $query = LogSuspectedUser::query()
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('content_type')
                    ->label('コンテンツタイプ')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return ContentType::tryFrom($record?->content_type)?->label() ?? '';
                        }
                    ),
                TextColumn::make('target_id')
                    ->label('降臨バトルID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cheat_type')
                    ->label('不正タイプ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('detail')
                    ->label('不正判定要因のデータ')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('suspected_at')
                    ->label('不正疑い日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('content_type')
                    ->options(ContentType::labels()->toArray())
                    ->query(function (Builder $query, $data): Builder {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->where('content_type', $data['value']);
                    })
                    ->label('コンテンツタイプ')
                    ->default($this->contentType ?? null),
                Filter::make('target_id')
                    ->form([
                        TextInput::make('target_id')
                            ->label('降臨バトルID')
                            ->default($this->targetId ?? null)
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['target_id'])) {
                            return $query;
                        }
                        return $query->where('target_id', $data['target_id']);
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('適用'),
            );
        ;
    }

    public function getAdmUserBanOperateHistoriesTableRows(): array
    {
        $admUserBanOperateHistoriesTableRows = [];

        $admUserBanOperateHistories = AdmUserBanOperateHistory::query()
            ->where('usr_user_id', $this->userId)
            ->orderBy('operated_at', 'desc')
            ->get();

        foreach ($admUserBanOperateHistories as $admUserBanOperateHistory) {
            $admUserBanOperateHistoriesTableRows[] = [
                'status' => UserStatus::tryFrom($admUserBanOperateHistory->ban_status)->label(),
                'operation_reason' => nl2br(htmlspecialchars($admUserBanOperateHistory->operation_reason)),
                'operated_at' => $admUserBanOperateHistory->operated_at,
            ];
        }

        return $admUserBanOperateHistoriesTableRows;
    }

    public function banPermanentInfoList(): Infolist
    {
        $admUserBanOperateHistory = AdmUserBanOperateHistory::query()
            ->where('usr_user_id', $this->userId)
            ->where('ban_status', UserStatus::BAN_PERMANENT->value)
            ->first();

        $state = [
            'operated_at' => $admUserBanOperateHistory->operated_at,
            'operation_reason' => $admUserBanOperateHistory->operation_reason,
        ];

        $fieldset = Fieldset::make('アカウント永久停止詳細')
            ->schema([
                Grid::make(1)->schema([
                    TextEntry::make('operation_reason')->label('アカウント永久停止経緯')
                ]),
                TextEntry::make('operated_at')
                    ->label('アカウント永久停止日時'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    protected function getActionButtons(): array
    {
        $usrUser = UsrUser::query()
            ->where('id', $this->userId)
            ->first();

        $admUserBanOperateHistory = AdmUserBanOperateHistory::getBanTemporariesByUsrUserId(
            $this->userId,
            'temporary_total_count'
        );

        $actions = [];

        // アカウント一時停止
        if ($usrUser->status === UserStatus::NORMAL->value) {
            $actions[] = FilamentAction::make('send')
                ->label('アカウント一時停止')
                ->color(UserStatus::BAN_TEMPORARY_CHEATING->color())
                ->url(function () use ($usrUser) {
                    return EditSuspendedUser::getUrl([
                        'userId' => $this->userId,
                        'status' => $usrUser->status
                    ]);
                });
        }

        // アカウント一時停止解除
        if (UserStatus::isTemporarySuspended($usrUser->status)) {
            $actions[] = FilamentAction::make('suspendedSend')
                ->label('アカウント一時停止解除')
                ->color(UserStatus::NORMAL->color())
                ->url(function () use ($usrUser) {
                    return UnSuspendedUser::getUrl([
                        'userId' => $this->userId,
                        'status' => $usrUser->status
                    ]);
                });
        }

        // アカウント永久停止
        if (
            UserStatus::isBanTemporaryStatus($usrUser->status)
            && $admUserBanOperateHistory->temporary_total_count > 0
        ) {
            $actions[] = FilamentAction::make('deactivatorSend')
                ->label('アカウント永久停止')
                ->color(UserStatus::BAN_PERMANENT->color())
                ->url(function () use ($usrUser) {
                    return UserDeactivator::getUrl([
                        'userId' => $this->userId,
                        'status' => $usrUser->status
                    ]);
                });
        }

        // アカウント永久停止解除
        if ($usrUser->status === UserStatus::BAN_PERMANENT->value) {
            $actions[] = FilamentAction::make('unDeactivatorsend')
                ->label('アカウント永久停止解除')
                ->color(UserStatus::NORMAL->color())
                ->url(function () use ($usrUser) {
                    return EditSuspendedUser::getUrl([
                        'userId' => $this->userId,
                        'status' => $usrUser->status
                    ]);
                });
        }

        return $actions;
    }
}
