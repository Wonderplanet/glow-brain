<?php

namespace App\Filament\Pages;

use App\Constants\ContentType;
use App\Filament\Resources\PvpSuspectedUsersResource;
use App\Models\Log\LogSuspectedUser;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PvpSuspectedUserList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.pvp-suspected-user-list';
    protected static ?string $title = 'ランクマッチ不正疑惑ユーザー一覧';
    protected static bool $shouldRegisterNavigation = false;

    public string $sysPvpSeasonId = '';

    protected $queryString = [
        'sysPvpSeasonId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            PvpSuspectedUsersResource::getUrl() => 'ランクマッチ不正疑惑操作',
            PvpSuspectedUserList::getUrl(['sysPvpSeasonId' => $this->sysPvpSeasonId]) => 'ランクマッチ不正疑惑ユーザー一覧',
        ]);
    }


    public function table(Table $table): Table
    {
        $sysPvpSeasonId = $this->sysPvpSeasonId;

        $query = LogSuspectedUser::query()
            ->select([
                'log_suspected_users.*',
                'usr_user_profiles.my_id as my_id',
                'usr_user_profiles.name as name',
                'usr_pvps.is_excluded_ranking as is_excluded_ranking',
            ])
            ->join('usr_pvps', function ($join) {
                $join->on('log_suspected_users.usr_user_id', '=', 'usr_pvps.usr_user_id')
                    ->on('log_suspected_users.target_id', '=', 'usr_pvps.sys_pvp_season_id');
            })
            ->join('usr_user_profiles', function ($join) {
                $join->on('log_suspected_users.usr_user_id', '=', 'usr_user_profiles.usr_user_id');
            })
            ->where('target_id', $sysPvpSeasonId)
            ->where('content_type', ContentType::PVP)
            ->whereIn('log_suspected_users.id', function($query) use ($sysPvpSeasonId) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('log_suspected_users')
                        ->where('target_id', $sysPvpSeasonId)
                        ->where('content_type', ContentType::PVP)
                        ->groupBy('usr_user_id');
                })
            ->orderBy('log_suspected_users.id');

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('usr_user_id')
                    ->label('ユーザーID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profile.my_id')
                    ->label('MY_ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usr_user_profile.name')
                    ->label('名前')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('ステータス')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return $record->is_excluded_ranking ? '除外中' : '通常';
                        }
                    ),
            ])
            ->filters([
                Filter::make('usr_user_id')
                    ->form([
                        TextInput::make('usr_user_id')
                            ->label('ユーザーID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['usr_user_id'])) {
                            return $query;
                        }
                        return $query->where('usr_user_id', 'like', "%{$data['usr_user_id']}%");
                    }),
                Filter::make('my_id')
                    ->form([
                        TextInput::make('my_id')
                            ->label('MY_ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['my_id'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('usr_user_profile', function ($query) use ($data) {
                                $query->where('my_id', 'like', "%{$data['my_id']}%");
                        });
                    }),
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('名前')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['name'])) {
                            return $query;
                        }
                        return $query
                            ->whereHas('usr_user_profile', function ($query) use ($data) {
                                $query->where('name', 'like', "%{$data['name']}%");
                        });
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action
                    ->label('適用'),
            )
            ->headerActions([
                BulkAction::make('usr_user_id')
                    ->label('一括復帰')
                    ->action(function ($records) use ($sysPvpSeasonId) {
                        $validRecords = $records->filter(function ($record) {
                            return $record->is_excluded_ranking;
                        });
                        if ($validRecords->isEmpty()) {
                            Notification::make()
                                ->title('ランキング除外されているユーザーがいません')
                                ->warning()
                                ->send();
                            return;
                        }

                        if ($validRecords->count() !== $records->count()) {
                            Notification::make()
                                ->title('ランキング除外されていないユーザーは除外しました')
                                ->warning()
                                ->send();
                        }

                        $usrUserIds = $validRecords->pluck('usr_user_id')->toArray();
                        redirect()->to(PvpSuspectedUser::getUrl([
                            'userIds' => implode(',', $usrUserIds),
                            'sysPvpSeasonId' => $sysPvpSeasonId,
                        ]));
                    })
            ])
            ->checkIfRecordIsSelectableUsing(
                fn ($record) => $record->is_excluded_ranking,
            );
    }
}
