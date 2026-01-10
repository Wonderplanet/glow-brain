<?php

namespace App\Filament\Pages;

use App\Constants\EmblemType;
use App\Constants\ImagePath;
use App\Constants\RarityType;
use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Usr\UsrEmblem;
use App\Tables\Columns\AssetImageColumn;
use App\Tables\Columns\MstIdColumn;
use App\Tables\Columns\MstSeriesInfoColumn;
use Filament\Actions\Action as FilamentAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserEmblem extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-emblem';

    public string $currentTab = UserSearchTabs::EMBLEM->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = UsrEmblem::query()
            ->where('usr_user_id', $this->userId)
            ->orderBy('created_at', 'desc')
            ->with([
                'mst_emblem',
                'mst_emblem.mst_emblem_i18n',
                'mst_emblem.mst_series',
                'mst_emblem.mst_series.mst_series_i18n',
            ]);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),
                MstIdColumn::make('mst_emblem_info')
                    ->label('エンブレム情報')
                    ->getMstUsing(function (UsrEmblem $model) {
                        return $model->mst_emblem;
                    })
                    ->getMstDetailPageUrlUsing(function (UsrEmblem $model) {
                        return EmblemDetail::getUrl(['mstEmblemId' => $model->mst_emblem_id]);
                    })
                    ,
                TextColumn::make('mst_emblem.emblem_type')
                    ->label('エンブレムタイプ'),
                MstSeriesInfoColumn::make('mst_series_info')
                    ->label('作品ID')
                    ->searchable()
                    ->getStateUsing(
                        function (UsrEmblem $model) {
                            return $model->mst_emblem?->mst_series ?? '';
                        }
                    ),
                TextColumn::make('created_at')
                    ->label('獲得日時'),
                TextColumn::make('updated_at')
                    ->label('最終更新日時'),
                ])
                ->filters([
                    Filter::make('id')
                        ->form([
                            TextInput::make('id')
                                ->label('ID')
                        ])
                        ->label('ID')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['id'])) {
                                return $query;
                            }
                            return $query->where('id', 'like', "%{$data['id']}%");
                        }),
                    Filter::make('mst_emblem_id')
                        ->form([
                            TextInput::make('mst_emblem_id')
                                ->label('エンブレムID')
                        ])
                        ->label('エンブレムID')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['mst_emblem_id'])) {
                                return $query;
                            }
                            return $query->where('mst_emblem_id', 'like', "%{$data['mst_emblem_id']}%");
                        }),
                    Filter::make('name')
                        ->form([
                            TextInput::make('name')
                                ->label('エンブレム名称')
                        ])
                        ->label('エンブレム名称')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['name'])) {
                                return $query;
                            }
                            return $query->whereHas('mst_emblem.mst_emblem_i18n', function ($query) use ($data) {
                                $query->where('name', 'like', "%{$data['name']}%");
                            });
                        }),
                    SelectFilter::make('emblem_type')
                        ->options(EmblemType::labels()->toArray())
                        ->query(function (Builder $query, $data): Builder {
                            if (blank($data['value'])) {
                                return $query;
                            }
                            return $query->whereHas('mst_emblem', function ($query) use ($data) {
                                $query->where('emblem_type', $data);
                            });
                        })
                        ->label('エンブレムタイプ'),
                    Filter::make('mst_series_id')
                        ->form([
                            TextInput::make('mst_series_id')
                                ->label('作品ID')
                        ])
                        ->label('作品ID')
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['mst_series_id'])) {
                                return $query;
                            }
                            return $query->whereHas('mst_emblem', function ($query) use ($data) {
                                $query->where('mst_series_id', 'like', "%{$data['mst_series_id']}%");
                            });
                        }),
                    Filter::make('series_name')
                        ->form([
                            TextInput::make('series_name')
                                ->label('作品名')
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (blank($data['series_name'])) {
                                return $query;
                            }
                            return $query
                                ->whereHas('mst_emblem', function ($query) use ($data) {
                                    $query->whereHas('mst_series', function ($query) use ($data) {
                                        $query->whereHas('mst_series_i18n', function ($query) use ($data) {
                                            $query->where('name', 'like', "%{$data['series_name']}%");
                                    });
                                });
                            });
                        }),
                    ], FiltersLayout::AboveContent)
                ->deferFilters()
                ->filtersApplyAction(
                    fn (Action $action) => $action
                        ->label('適用'),
                )
                ->actions([]);
    }

    protected function getActions(): array
    {
        return [
            FilamentAction::make('send')
                ->label('付与')
                ->url(function () {
                    return SendUserEmblem::getUrl([
                        'userId' => $this->userId,
                    ]);
                })
                ->visible(fn () => SendUserEmblem::canAccess())
        ];
    }
}
