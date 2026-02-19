<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Constants\BoxGachaLogType;
use App\Filament\Actions\SimpleCsvDownloadAction;
use App\Filament\Authorizable;
use App\Filament\Pages\User\UserDataBasePage;
use App\Filament\Tables\Columns\DateTimeColumn;
use App\Models\Log\LogBoxGachaAction;
use App\Models\Mst\MstBoxGachaPrize;
use App\Traits\AthenaQueryTrait;
use App\Traits\RewardInfoGetTrait;
use App\Traits\UserLogTableFilterTrait;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class UserLogBoxGachaAction extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;
    use Authorizable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-box-gacha-action';

    public string $currentTab = UserSearchTabs::LOG_BOX_GACHA_ACTION->value;

    public function mount()
    {
        parent::mount();

        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    public function table(Table $table): Table
    {
        $query = LogBoxGachaAction::query()
            ->with([
                'mst_box_gacha.mst_event.mst_event_i18n',
            ])
            ->where('usr_user_id', $this->userId);

        return $table
            ->query($query)
            ->searchable(false)
            ->hiddenFilterIndicators()
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('log_type')->label('ログタイプ')->sortable()
                    ->badge()
                    ->color(fn (string $state): string => BoxGachaLogType::toBadgeColor($state)),
                TextColumn::make('mst_box_gacha_id')
                    ->label('BOXガシャID')
                    ->sortable()
                    ->url(fn (LogBoxGachaAction $record) => MstBoxGachaDetail::getUrl([
                        'mstBoxGachaId' => $record->mst_box_gacha_id,
                    ]), true),
                TextColumn::make('mst_box_gacha.mst_event.mst_event_i18n.name')
                    ->label('イベント名')
                    ->formatStateUsing(fn (LogBoxGachaAction $record) =>
                        $record->mst_box_gacha?->mst_event?->mst_event_i18n?->name ?? ''
                    )
                    ->url(fn (LogBoxGachaAction $record) => $record->mst_box_gacha?->mst_event_id !== null
                        ? EventDetail::getUrl(['mstEventId' => $record->mst_box_gacha?->mst_event_id])
                        : null, true),
                TextColumn::make('total_draw_count')
                    ->label('総抽選回数')
                    ->sortable()
                    ->color('primary')
                    ->action(
                        Action::make('view_draw_prizes')
                            ->modalHeading('抽選結果一覧')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('閉じる')
                            ->modalContent(fn (LogBoxGachaAction $record) => $this->renderDrawPrizesModal($record->draw_prizes))
                    ),
                DateTimeColumn::make('created_at')->label('実行日時')->sortable(),
            ])
            ->filters([
                SelectFilter::make('log_type')
                    ->label('ログタイプ')
                    ->options(BoxGachaLogType::options()),
                ...$this->getCommonLogFilters(),
            ], FiltersLayout::AboveContent)
            ->deferFilters()
            ->filtersApplyAction(
                fn (Action $action) => $action->label('検索'),
            )
            ->headerActions([
                SimpleCsvDownloadAction::make()
                    ->fileName('user_log_box_gacha_action')
            ]);
    }

    private function renderDrawPrizesModal(?string $drawPrizesJson): View
    {
        $drawPrizes = $this->parseDrawPrizes($drawPrizesJson);

        if (empty($drawPrizes)) {
            return view('filament.pages.partials.log-draw-prizes-modal', [
                'prizes' => [],
            ]);
        }

        $prizes = $this->buildPrizesData($drawPrizes);

        return view('filament.pages.partials.log-draw-prizes-modal', [
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
     * LogBoxGachaActionのdraw_prizes形式: [{"mstBoxGachaPrizeId": xxx, "drawCount": n}, ...]
     */
    private function buildPrizesData(array $drawPrizes): array
    {
        $prizeIds = array_column($drawPrizes, 'mstBoxGachaPrizeId');
        $mstPrizes = MstBoxGachaPrize::whereIn('id', $prizeIds)->get()->keyBy('id');

        $rewardDtos = $mstPrizes->map(fn ($prize) => $prize->getRewardDto());
        $rewardInfos = $rewardDtos->isNotEmpty()
            ? $this->getRewardInfos($rewardDtos)
            : collect();

        $prizes = [];
        foreach ($drawPrizes as $drawPrize) {
            $prizeId = $drawPrize['mstBoxGachaPrizeId'] ?? null;
            $count = $drawPrize['drawCount'] ?? 0;

            if ($prizeId === null) {
                continue;
            }

            $rewardInfo = $rewardInfos->get($prizeId);

            $prizes[] = [
                'prizeId' => $prizeId,
                'reward' => $rewardInfo?->getLabelWithAmount() ?? '-',
                'count' => $count,
            ];
        }

        return $prizes;
    }
}
