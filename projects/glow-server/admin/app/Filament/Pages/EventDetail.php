<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstEventResource;
use App\Models\Mst\MstEvent;
use App\Models\Mst\MstMissionEventDailyBonus;
use App\Models\Mst\MstMissionEventDailyBonusSchedule;
use App\Models\Mst\MstMissionReward;
use App\Models\Mst\MstQuest;
use App\Models\Mst\MstSeries;
use App\Services\Reward\RewardInfoGetHandleService;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EventDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.event-detail';
    protected static ?string $title = 'イベント詳細';

    private static ?RewardInfoGetHandleService $rewardInfoGetHandleService = null;

    private static Collection $rewardInfos;

    private static function initializeService(): void
    {
        if (is_null(self::$rewardInfoGetHandleService)) {
            self::$rewardInfoGetHandleService = app(RewardInfoGetHandleService::class);
        }
    }

    public string $mstEventId = '';

    protected $queryString = [
        'mstEventId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstEventResource::class;
    }

    protected function getMstModelByQuery(): ?MstEvent
    {
        return MstEvent::query()
            ->where('id', $this->mstEventId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('イベントID: %s', $this->mstEventId);
    }

    protected function getSubTitle(): string
    {
        $mstEvent = $this->getMstModel();

        return StringUtil::makeIdNameViewString(
            $mstEvent->id,
            $mstEvent->getName(),
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MstEvent::query())
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    public function infoList(): InfoList
    {
        $mstEvent = $this->getMstModel();
        $mstEventI18n = $mstEvent->mst_event_i18n;

        $mstSeries = MstSeries::query()
            ->with('mst_series_i18n')
            ->where('id', $mstEvent->mst_series_id)
            ->first();

        $state = [
            'id'                        => $mstEvent->id,
            'name'                      => $mstEventI18n?->name ?? '',
            'mst_series_id'             => '[' . $mstEvent->mst_series_id . '] ' . ($mstSeries?->mst_series_i18n?->name ?? ''),
            'is_displayed_series_logo'  => $mstEvent->is_displayed_series_logo ? '表示あり' : '表示なし',
            'is_displayed_jump_plus'    => $mstEvent->is_displayed_jump_plus ? '表示あり' : '表示なし',
            'start_at'                  => $mstEvent->start_at,
            'end_at'                    => $mstEvent->end_at,
            'asset_key'                 => $mstEvent->asset_key,
            'release_key'               => $mstEvent->release_key,
        ];

        $fieldset = Fieldset::make('イベント詳細')
            ->schema([
                TextEntry::make('id')->label('イベントID'),
                TextEntry::make('name')->label('イベント名'),
                TextEntry::make('is_displayed_series_logo')->label('作品ロゴの表示有無'),
                TextEntry::make('is_displayed_jump_plus')->label('作品を読むボタンの表示有無'),
                TextEntry::make('start_at')->label('開始日'),
                TextEntry::make('end_at')->label('終了日'),
                TextEntry::make('mst_series_id')->label('作品情報'),
                TextEntry::make('asset_key')->label('アセットキー'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);


        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function eventQuestTable(): ?Table
    {
        $query = MstQuest::query()
            ->with('mst_quest_i18n')
            ->where('mst_event_id', $this->mstEventId);

        return $this->getTable()
            ->heading('イベントクエスト')
            ->query($query)
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('mst_quest_i18n.name')->label('クエスト名'),
                TextColumn::make('sort_order')->label('並び順'),
                TextColumn::make('start_date')->label('開始日'),
                TextColumn::make('end_date')->label('終了日'),
            ])
            ->paginated(false)
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (Model $record) {
                        return QuestDetail::getUrl([
                            'questId' => $record->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns);
    }

    public function eventLoginBonusTable(): array
    {
        $mstMissionEventDailyBonusSchedules = MstMissionEventDailyBonusSchedule::query()
            ->where('mst_event_id', $this->mstEventId)
            ->get();

        $mst_mission_event_daily_bonus_schedule_ids = $mstMissionEventDailyBonusSchedules->pluck('id')->toArray();
        $mstMissionEventDailyBonus = MstMissionEventDailyBonus::query()
            ->with([
                'mst_mission_rewards',
            ])
            ->whereIn('mst_mission_event_daily_bonus_schedule_id', $mst_mission_event_daily_bonus_schedule_ids)
            ->get();

        $rewardDtoList = MstMissionReward::query()->get()->map(function (MstMissionReward $mstMissionReward) {
            return $mstMissionReward->reward;
        });
        self::initializeService();
        self::$rewardInfos = self::$rewardInfoGetHandleService->build($rewardDtoList)->getRewardInfos();

        $mstMissionEventDailyBonuslist = [];
        foreach ($mstMissionEventDailyBonusSchedules as $mstMissionEventDailyBonusSchedule) {

            foreach ($mstMissionEventDailyBonus as $value) {

                if($mstMissionEventDailyBonusSchedule->id !== $value->mst_mission_event_daily_bonus_schedule_id){
                    continue;
                }

                $rewardInfo = [];
                foreach ($value->mst_mission_rewards as $reward) {
                    $rewardInfo[] = self::$rewardInfos->get($reward->id);
                }
                $mstMissionEventDailyBonuslist[$value->mst_mission_event_daily_bonus_schedule_id]['day'] = [
                    'start_at'          => $mstMissionEventDailyBonusSchedule->start_at,
                    'end_at'            => $mstMissionEventDailyBonusSchedule->end_at,
                ];
                $mstMissionEventDailyBonuslist[$value->mst_mission_event_daily_bonus_schedule_id]['info'][] = [
                    'id'                => $value->id,
                    'login_day_count'   => $value->login_day_count,
                    'rewardInfo'        => $rewardInfo,
                ];
            }
        }

        return $mstMissionEventDailyBonuslist;
    }
}
