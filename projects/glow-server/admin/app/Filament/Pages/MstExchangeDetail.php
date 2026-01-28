<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstExchangeResource;
use App\Models\Mst\MstExchange;
use App\Models\Mst\MstExchangeLineup;
use App\Tables\Columns\RewardInfoColumn;
use App\Traits\RewardInfoGetTrait;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class MstExchangeDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;
    use RewardInfoGetTrait;

    protected static string $view = 'filament.pages.mst-exchange-detail';

    protected static ?string $title = '交換所詳細';

    public string $mstExchangeId = '';

    protected $queryString = [
        'mstExchangeId',
    ];

    protected Collection $rewardInfos;
    protected Collection $costInfos;

    public function mount()
    {
        parent::mount();

        $this->createRewardAndCostInfos();
    }

    protected function getResourceClass(): ?string
    {
        return MstExchangeResource::class;
    }

    protected function getMstModelByQuery(): ?MstExchange
    {
        return MstExchange::query()
            ->with('mst_exchange_i18n')
            ->where('id', $this->mstExchangeId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('交換所ID: %s', $this->mstExchangeId);
    }

    protected function getSubTitle(): string
    {
        $mstExchange = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $this->mstExchangeId,
            $mstExchange->mst_exchange_i18n?->name ?? '',
        );
    }

    private function createRewardAndCostInfos(): void
    {
        $mstExchange = $this->getMstModel();
        if ($mstExchange === null) {
            return;
        }

        // 全てのラインナップを取得
        $mstExchangeLineups = MstExchangeLineup::query()
            ->with(['rewards', 'costs'])
            ->where('group_id', $mstExchange->lineup_group_id)
            ->get();

        // 報酬とコスト用のDTOを作成（rewardアクセサとcostアクセサを使用）
        $rewardDtos = collect();
        $costDtos = collect();
        foreach ($mstExchangeLineups as $lineup) {
            foreach ($lineup->rewards as $mstExchangeReward) {
                $rewardDtos->push($mstExchangeReward->reward);
            }
            foreach ($lineup->costs as $mstExchangeCost) {
                $costDtos->push($mstExchangeCost->cost);
            }
        }

        // RewardInfoコレクションを作成（フォールバック処理付き）
        $this->rewardInfos = $rewardDtos->isEmpty()
            ? collect()
            : $this->getRewardInfos($rewardDtos);

        $this->costInfos = $costDtos->isEmpty()
            ? collect()
            : $this->getRewardInfos($costDtos);
    }

    public function infoList(): InfoList
    {
        $mstExchange = $this->getMstModel();
        $mstExchangeI18n = $mstExchange->mst_exchange_i18n;

        $state = [
            'id' => $mstExchange->id,
            'name' => $mstExchangeI18n?->name ?? '',
            'banner_url' => $mstExchangeI18n?->banner_url ?? '',
            'lineup_group_id' => $mstExchange->lineup_group_id,
            'start_at' => $mstExchange->start_at,
            'end_at' => $mstExchange->end_at ?? '無期限',
            'display_order' => $mstExchange->display_order,
            'release_key' => $mstExchange->release_key,
        ];

        $fieldset = Fieldset::make('交換所詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name')->label('交換所名'),
                TextEntry::make('banner_url')->label('バナーURL'),
                TextEntry::make('lineup_group_id')->label('ラインナップグループID'),
                TextEntry::make('start_at')->label('開始日時'),
                TextEntry::make('end_at')->label('終了日時'),
                TextEntry::make('display_order')->label('表示順序'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function table(Table $table): Table
    {
        $mstExchange = $this->getMstModel();

        // 交換ラインナップ一覧を表示
        $query = MstExchangeLineup::query()
            ->with(['rewards', 'costs'])
            ->where('group_id', $mstExchange->lineup_group_id);

        return $table
            ->heading('交換ラインナップ一覧')
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('ラインナップID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tradable_count')
                    ->label('交換上限数')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(
                        function (MstExchangeLineup $mstExchangeLineup) {
                            return $mstExchangeLineup->tradable_count ?? '無制限';
                        }
                    ),
                TextColumn::make('display_order')
                    ->label('表示順序')
                    ->searchable()
                    ->sortable(),
                RewardInfoColumn::make('rewards_info')
                    ->label('報酬情報')
                    ->getStateUsing(
                        function (MstExchangeLineup $mstExchangeLineup) {
                            if ($mstExchangeLineup->rewards->isEmpty()) {
                                return collect();
                            }

                            $rewardInfoList = collect();
                            foreach ($mstExchangeLineup->rewards as $mstExchangeReward) {
                                $rewardInfoId = $mstExchangeReward->reward->getId();
                                $rewardInfo = $this->rewardInfos->get($rewardInfoId);
                                if ($rewardInfo !== null) {
                                    $rewardInfoList->push($rewardInfo);
                                }
                            }
                            return $rewardInfoList;
                        }
                    ),
                RewardInfoColumn::make('costs_info')
                    ->label('コスト情報')
                    ->getStateUsing(
                        function (MstExchangeLineup $mstExchangeLineup) {
                            if ($mstExchangeLineup->costs->isEmpty()) {
                                return collect();
                            }

                            $costInfoList = collect();
                            foreach ($mstExchangeLineup->costs as $mstExchangeCost) {
                                $costInfoId = $mstExchangeCost->cost->getId();
                                $costInfo = $this->costInfos->get($costInfoId);
                                if ($costInfo !== null) {
                                    $costInfoList->push($costInfo);
                                }
                            }
                            return $costInfoList;
                        }
                    ),
            ])
            ->paginated(false);
    }
}
