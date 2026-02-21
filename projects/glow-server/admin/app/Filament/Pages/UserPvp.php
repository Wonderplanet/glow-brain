<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrPvp;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class UserPvp extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-pvp';

    public string $currentTab = UserSearchTabs::PVP->value;

    public function mount(): void
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function getUsrPvpTable(): Table
    {
        return $this->table($this->getTable());
    }

    private function table(Table $table): Table
    {
        $query = UsrPvp::query()
            ->where('usr_user_id', $this->userId)
            ->orderBy('sys_pvp_season_id', 'desc')
            ->with([
                'sys_pvp_seasons',
                'sys_pvp_seasons.mst_pvps',
                'sys_pvp_seasons.mst_pvps.mst_pvp_i18n',
            ]);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('sys_pvp_season_id')
                    ->label('ランクマッチシーズンID'),
                TextColumn::make('sys_pvp_seasons.mst_pvp_id')
                    ->label('ランクマッチID'),
                TextColumn::make('sys_pvp_seasons.mst_pvps.mst_pvp_i18n.name')
                    ->label('ランクマッチ名'),
                TextColumn::make('pvp_rank_class_type')
                    ->label('ランク名'),
                TextColumn::make('pvp_rank_class_level')
                    ->label('ランクレベル'),
                TextColumn::make('score')
                    ->label('ランクポイント'),
                TextColumn::make('ranking')
                    ->label('ランキング'),
                TextColumn::make('daily_remaining_challenge_count')
                    ->label(new HtmlString('残りアイテム<br>消費なし<br>挑戦可能回数')),
                TextColumn::make('daily_remaining_item_challenge_count')
                    ->label(new HtmlString('残りアイテム<br>消費あり<br>挑戦可能回数')),
                TextColumn::make('is_season_reward_received')
                    ->label(new HtmlString('シーズン報酬<br>受け取り済みか'))
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? '受取済' : '未受取'),
            ])
            ->filters([
                Filter::make('pvp_id')
                    ->label('ランクマッチID')
                    ->form([
                        TextInput::make('pvp_id')->label('ランクマッチID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['pvp_id'])) {
                            return $query;
                        }
                        return $query->whereHas('sys_pvp_seasons', function ($query) use ($data) {
                            return $query->where('mst_pvp_id', 'like', "%{$data['pvp_id']}%");
                        });
                    }),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->actions([
                Action::make('edit')
                    ->label('編集')
                    ->button()
                    ->url(function (UsrPvp $record) {
                        return EditUserPvp::getUrl([
                            'userId' => $this->userId,
                            'sysPvpSeasonId' => $record->sys_pvp_season_id,
                        ]);
                    })
                    ->visible(fn () => EditUserPvp::canAccess()),
            ], position: ActionsPosition::BeforeColumns);
    }
}
