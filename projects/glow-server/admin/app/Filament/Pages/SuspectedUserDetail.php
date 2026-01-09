<?php

namespace App\Filament\Pages;

use App\Constants\ContentType;
use App\Constants\UserSearchTabs;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Log\LogSuspectedUser;
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

class SuspectedUserDetail extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.suspected-user-detail';
    protected static bool $shouldRegisterNavigation = false;
    public string $currentTab = UserSearchTabs::SUSPECTED->value;

    public string $userId = '';
    public string $contentType = '';
    public string $targetId = '';

    protected $queryString = [
        'userId',
        'contentType',
        'targetId',
    ];

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
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
                    ->label('コンテンツID')
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
                            ->label('コンテンツID')
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
                fn (Action $action) => $action
                    ->label('適用'),
            );
        ;
    }
}
