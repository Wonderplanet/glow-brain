<?php

namespace App\Filament\Pages;

use App\Filament\Pages\User\UserDataBasePage;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Contracts\HasTable;
use App\Constants\UserSearchTabs;
use App\Constants\GachaCostType;
use App\Filament\Actions\CustomCsvDownloadAction;
use App\Models\Log\LogGachaAction;
use App\Models\Mst\OprGacha;
use App\Models\Mst\OprGachaPrize;
use App\Tables\Columns\OprGachaInfoColumn;
use App\Traits\RewardInfoGetTrait;
use App\Tables\Columns\GachaPrizesInfoColumn;
use App\Traits\AthenaQueryTrait;
use App\Traits\UserLogTableFilterTrait;

class UserLogGachaAction extends UserDataBasePage implements HasTable
{
    use AthenaQueryTrait;
    use UserLogTableFilterTrait;
    use RewardInfoGetTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-gacha-action';
    public string $currentTab = UserSearchTabs::LOG_GACHA_ACTION->value;

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);
    }

    private function table(Table $table)
    {
        $query = LogGachaAction::query()
            ->with([
                'log_gacha',
            ])
            ->where('usr_user_id', $this->userId)
            ->orderBy('created_at', 'asc');

        $oprGachas = OprGacha::query()
            ->with('opr_gacha_i18n')
            ->get()
            ->keyBy('id');

        $oprGachaPrizes = OprGachaPrize::query()
            ->get();

        $prizeResources = $oprGachaPrizes->map(function ($oprGachaPrize) {
            return $oprGachaPrize->prize_resource;
        });

        $prizeResources = $this->getRewardInfos($prizeResources);

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('引いた日時')
                    ->dateTime('Y-m-d H:i:s')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nginx_request_id')
                    ->label('APIリクエストID')
                    ->searchable()
                    ->sortable(),
                OprGachaInfoColumn::make('opr_gacha_id')
                    ->label('ガシャ情報')
                    ->searchable()
                    ->getStateUsing(
                        function ($record) use ($oprGachas) {
                            return $oprGachas->get($record->opr_gacha_id);
                        }
                    )
                    ->sortable(),
                TextColumn::make('cost_type')
                    ->label('消費コスト情報')
                    ->getStateUsing(
                        function ($record) {
                            $costType = GachaCostType::tryFrom($record->cost_type);
                            return $costType?->label() ?? $record->cost_type;
                        }
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('draw_count')
                    ->label('排出数')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('max_rarity_upper_count')
                    ->label('回す前の最高レア天井カウント')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pickup_upper_count')
                    ->label('回す前のピックアップ天井カウント')
                    ->searchable()
                    ->sortable(),
                GachaPrizesInfoColumn::make('log_gacha.result')
                    ->label('排出物情報')
                    ->getStateUsing(
                        function ($record) use ($prizeResources) {
                            if ($record?->log_gacha) {
                                $results = unserialize($record->log_gacha->result);
                                $rewards = [];
                                foreach ($results as $result) {
                                    $rewards[] = $prizeResources->get($result['id']);
                                }
                                return array_filter($rewards);
                            }
                            return;
                        }
                    )
                    ->searchable()
                    ->sortable(),

            ])
            ->filters(
                $this->getCommonLogFilters(),
                FiltersLayout::AboveContent)
            ->deferFilters()
            ->hiddenFilterIndicators()
            ->filtersApplyAction(
                fn(Action $action) => $action
                    ->label('検索'),
            )
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CustomCsvDownloadAction::make()
                    ->fileName('user_log_gacha')
                    ->withContext(['prizeResources' => $prizeResources])
            ]);
    }
}
