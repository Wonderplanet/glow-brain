<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Filament\Authorizable;
use App\Filament\Pages\EventDetail;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Mst\MstBoxGacha;
use App\Models\Mst\MstBoxGachaPrize;
use App\Models\Usr\UsrBoxGacha;
use App\Traits\RewardInfoGetTrait;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class UserBoxGacha extends UserDataBasePage implements HasTable
{
    use InteractsWithTable;
    use RewardInfoGetTrait;
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-box-gacha';

    public string $currentTab = UserSearchTabs::BOX_GACHA->value;

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters(), FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->deferFilters()
            ->filtersApplyAction(fn (Action $action) => $action->label('適用'))
            ->actions($this->getTableActions(), position: ActionsPosition::BeforeColumns);
    }

    private function getTableQuery(): Builder
    {
        return UsrBoxGacha::query()
            ->with(['mst_box_gacha.mst_event.mst_event_i18n'])
            ->where('usr_user_id', $this->userId);
    }

    private function getTableColumns(): array
    {
        return [
            ColumnGroup::make('BOX情報', [
                TextColumn::make('mst_box_gacha_id')
                    ->label('BOXガシャID')
                    ->sortable()
                    ->url(fn (UsrBoxGacha $record) => MstBoxGachaDetail::getUrl([
                        'mstBoxGachaId' => $record->mst_box_gacha_id,
                    ])),
                TextColumn::make('mst_box_gacha.mst_event.mst_event_i18n.name')
                    ->label('イベント名')
                    ->formatStateUsing(fn (UsrBoxGacha $record) =>
                        $record->mst_box_gacha?->mst_event?->mst_event_i18n?->name ?? ''
                    )
                    ->url(fn (UsrBoxGacha $record) => $record->mst_box_gacha?->mst_event_id !== null
                        ? EventDetail::getUrl(['mstEventId' => $record->mst_box_gacha?->mst_event_id])
                        : null),
            ]),
            ColumnGroup::make('進捗', [
                TextColumn::make('current_box_level')->label('現在BOXレベル')->sortable(),
                TextColumn::make('reset_count')->label('リセット回数')->sortable(),
            ]),
            ColumnGroup::make('抽選', [
                TextColumn::make('total_draw_count')->label('総抽選回数')->sortable(),
                TextColumn::make('draw_count')
                    ->label('現在BOX抽選回数')
                    ->sortable()
                    ->color('primary')
                    ->action($this->getDrawPrizesModalAction()),
            ]),
            TextColumn::make('updated_at')->label('最終更新日時')->dateTime()->sortable(),
        ];
    }

    private function getTableFilters(): array
    {
        return [
            Filter::make('mst_box_gacha_id')
                ->form([
                    TextInput::make('mst_box_gacha_id')->label('BOXガシャID')
                ])
                ->label('BOXガシャID')
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['mst_box_gacha_id'])) {
                        return $query;
                    }
                    return $query->where('mst_box_gacha_id', $data['mst_box_gacha_id']);
                }),
            Filter::make('event_name')
                ->form([
                    TextInput::make('event_name')->label('イベント名')
                ])
                ->label('イベント名')
                ->query(function (Builder $query, array $data): Builder {
                    if (blank($data['event_name'])) {
                        return $query;
                    }

                    $mstBoxGachaIds = MstBoxGacha::query()
                        ->whereHas('mst_event.mst_event_i18n', function (Builder $query) use ($data) {
                            return $query->where('name', 'like', "%{$data['event_name']}%");
                        })
                        ->pluck('id');

                    return $query->whereIn('mst_box_gacha_id', $mstBoxGachaIds);
                }),
        ];
    }

    private function getTableActions(): array
    {
        return [
            Action::make('edit')
                ->label('編集')
                ->button()
                ->url(fn (UsrBoxGacha $record) => EditUserBoxGacha::getUrl([
                    'userId' => $this->userId,
                    'mstBoxGachaId' => $record->mst_box_gacha_id,
                ]))
                ->visible(fn () => EditUserBoxGacha::canAccess()),
        ];
    }

    private function getDrawPrizesModalAction(): Action
    {
        return Action::make('view_draw_prizes')
            ->modalHeading('抽選済み賞品一覧')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('閉じる')
            ->modalContent(fn (UsrBoxGacha $record) => $this->renderDrawPrizesModal($record->draw_prizes));
    }

    /**
     * 抽選済み賞品のモーダル用Viewを生成
     */
    private function renderDrawPrizesModal(?string $drawPrizesJson): View
    {
        $drawPrizes = $this->parseDrawPrizes($drawPrizesJson);

        if (empty($drawPrizes)) {
            return view('filament.pages.partials.draw-prizes-modal', [
                'prizes' => [],
            ]);
        }

        $prizes = $this->buildPrizesData($drawPrizes);

        return view('filament.pages.partials.draw-prizes-modal', [
            'prizes' => $prizes,
        ]);
    }

    private function parseDrawPrizes(?string $drawPrizesJson): array
    {
        if (empty($drawPrizesJson)) {
            return [];
        }

        $parsed = json_decode($drawPrizesJson, true);

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * 賞品データを構築
     * @return array<int, array{prizeId: string, reward: string, count: int, stock: int|string}>
     */
    private function buildPrizesData(array $drawPrizes): array
    {
        $prizeIds = array_keys($drawPrizes);
        $mstPrizes = MstBoxGachaPrize::whereIn('id', $prizeIds)->get()->keyBy('id');

        $rewardDtos = $mstPrizes->map(fn ($prize) => $prize->getRewardDto());
        $rewardInfos = $rewardDtos->isNotEmpty()
            ? $this->getRewardInfos($rewardDtos)
            : collect();

        $prizes = [];
        foreach ($drawPrizes as $prizeId => $count) {
            $mstPrize = $mstPrizes->get($prizeId);
            $rewardInfo = $rewardInfos->get($prizeId);

            $prizes[] = [
                'prizeId' => $prizeId,
                'reward' => $rewardInfo?->getLabelWithAmount() ?? '-',
                'count' => $count,
                'stock' => $mstPrize?->stock ?? 'x',
            ];
        }

        return $prizes;
    }
}
