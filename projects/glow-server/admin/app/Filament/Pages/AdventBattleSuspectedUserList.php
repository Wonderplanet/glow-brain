<?php

namespace App\Filament\Pages;

use App\Constants\ContentType;
use App\Filament\Resources\AdventBattleSuspectedUsersResource;
use App\Models\Log\LogSuspectedUser;
use Filament\Forms\Components\TextInput;
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

class AdventBattleSuspectedUserList extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.advent-battle-suspected-user-list';
    protected static ?string $title = '降臨バトル不正疑惑ユーザー一覧';
    protected static bool $shouldRegisterNavigation = false;

    public string $mstAdventBattleId = '';

    protected $queryString = [
        'mstAdventBattleId',
    ];

    protected array $breadcrumbList = [];

    public function mount()
    {
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            AdventBattleSuspectedUsersResource::getUrl() => '降臨バトル不正疑惑操作',
            AdventBattleSuspectedUserList::getUrl(['mstAdventBattleId' => $this->mstAdventBattleId]) => '降臨バトル不正疑惑ユーザー一覧',
        ]);
    }


    public function table(Table $table): Table
    {

        $mstAdventBattleId = $this->mstAdventBattleId;

        $query = LogSuspectedUser::query()
            ->where('target_id', $mstAdventBattleId)
            ->where('content_type', ContentType::ADVENT_BATTLE)
            ->whereIn('id', function($query) use ($mstAdventBattleId) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('log_suspected_users')
                        ->where('target_id', $mstAdventBattleId)
                        ->where('content_type', ContentType::ADVENT_BATTLE)
                        ->groupBy('usr_user_id');
                })
            ->orderBy('id')
            ->with(
                'usr_user',
                'usr_user_profile',
                'usr_advent_battle'
            );

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
                TextColumn::make('usr_user.status')
                    ->label('ステータス')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function ($record) {
                            return $record?->usr_advent_battle?->is_excluded_ranking ? '除外中' : '通常';
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
                    ->action(function ($records) use ($mstAdventBattleId) {
                        if ($records && $records->isNotEmpty()) {
                            $usrUserIds = $records->pluck('usr_user_id')->toArray();
                            return redirect()->
                                to(AdventBattleSuspectedUser::getUrl([
                                    'userIds' => implode(',', $usrUserIds),
                                    'mstAdventBattleId' => $mstAdventBattleId,
                                ]));
                        }
                    })
            ]);
        ;

    }
}
