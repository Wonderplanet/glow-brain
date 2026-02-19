<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Models\Log\LogArtworkFragment;
use App\Models\Mst\MstArtworkFragmentI18n;
use App\Models\Mst\MstStageI18n;
use App\Models\Mst\MstAdventBattleI18n;
use App\Tables\Columns\MstIdColumn;
use App\Constants\UserSearchTabs;
use Filament\Tables\Filters\Filter;
use App\Filament\Actions\SimpleCsvDownloadAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Domain\Resource\Enums\InGameContentType;

class UserLogArtworkFragment extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-artwork-fragment';
    public string $currentTab = UserSearchTabs::LOG_ARTWORK_FRAGMENT->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogArtworkFragment::query()
            ->where('usr_user_id', $this->userId);
        $mstArtworkFragmentNameList = MstArtworkFragmentI18n::get()->pluck("name", "mst_artwork_fragment_id");
        $mstStageNameList = MstStageI18n::get()->pluck("name", "mst_stage_id");
        $mstdventBattleNameList = MstAdventBattleI18n::get()->pluck("name", "mst_advent_battle_id");

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                MstIdColumn::make('artwork_fragment_info')
                    ->label('入手原画のかけら情報')
                    ->searchable()
                    ->getMstIdUsing(
                        function ($record) {
                            return $record->mst_artwork_fragment_id;
                        }
                    )
                    ->getMstDataNameUsing(
                        function ($record) use ($mstArtworkFragmentNameList) {
                            return $mstArtworkFragmentNameList[$record->mst_artwork_fragment_id] ?? '未設定';
                        }
                    )
                    ->getMstDetailPageUrlUsing(function ($record) {
                        return MstArtworkDetail::getUrl([
                            'mstArtworkId' => $record->mst_artwork_fragment_id
                        ]);
                    }),
                MstIdColumn::make('get_contents_info')
                    ->label('入手先コンテンツ情報')
                    ->searchable()
                    ->getMstIdUsing(
                        function ($record) {
                            return $record->target_id;
                        }
                    )
                    ->getMstDataNameUsing(
                        function ($record) use ($mstStageNameList, $mstdventBattleNameList) {
                            if ($record->content_type === InGameContentType::STAGE->value) {
                                return $mstStageNameList[$record->target_id] ?? '未設定';
                            } else if ($record->content_type === InGameContentType::ADVENT_BATTLE->value) {
                                return $mstdventBattleNameList[$record->target_id] ?? '未設定';
                            }
                        }
                    )
                    ->getMstDetailPageUrlUsing(function ($record) {
                        if ($record->content_type === InGameContentType::STAGE->value) {
                            return StageDetail::getUrl([
                                'stageId' => $record->target_id
                            ]);
                        } else if ($record->content_type === InGameContentType::ADVENT_BATTLE->value) {
                            return MstAdventBattleDetail::getUrl([
                                'mstAdventBattleId' => $record->target_id
                            ]);
                        }
                    }),
                TextColumn::make('is_complete_artwork')
                    ->label('完成したかどうか')
                    ->getStateUsing(function ($record): string {
                        return $record->is_complete_artwork ? '完成' : '未完成';
                    }),
                TextColumn::make('created_at')
                    ->label('入手日時')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters(
                array_merge(
                    $this->getCommonLogFilters(),
                    [
                        Filter::make('mst_artwork_fragment_id')
                        ->form([
                            TextInput::make('mst_artwork_fragment_id')
                                ->label('原画のかけらID')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['mst_artwork_fragment_id'])) {
                                return $query;
                            }
                            return $query->where('mst_artwork_fragment_id', 'like', "{$data['mst_artwork_fragment_id']}%");
                        }),
                        Filter::make('content_type')
                        ->form([
                            TextInput::make('content_type')
                                ->label('入手先コンテンツタイプ')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['content_type'])) {
                                return $query;
                            }
                            return $query->where('content_type', 'like', "{$data['content_type']}%");
                        }),
                        Filter::make('content_id')
                        ->form([
                            TextInput::make('content_id')
                                ->label('入手先コンテンツID')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['content_id'])) {
                                return $query;
                            }
                            return $query->where('content_id', 'like', "{$data['content_id']}%");
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
                        ->fileName('user_log_artwork_fragment')
                ]);
    }
}
