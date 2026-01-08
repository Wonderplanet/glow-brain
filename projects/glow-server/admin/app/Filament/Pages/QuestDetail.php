<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstQuestResource;
use App\Infolists\Components\AssetBannerImageEntry;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstQuest;
use App\Models\Mst\MstStage;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class QuestDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.quest-detail';

    protected static ?string $title = 'クエスト詳細';

    public string $questId = '';

    protected $queryString = [
        'questId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstQuestResource::class;
    }

    protected function getMstModelByQuery(): ?MstQuest
    {
        return MstQuest::query()->where('id', $this->questId)->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('クエストID: %s', $this->questId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->questId,
            $this->getMstModel()?->mst_quest_i18n?->name ?? '',
        );
    }

    public static function getMainTitle()
    {
        return self::$title;
    }

    public function getStageTable(): Table
    {
        return $this->table($this->getTable());
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $mstQuest = $this->getMstModel();
        if ($mstQuest === null) {
            return $infolist;
        }

        $infolist
            ->state([
                'id' => $mstQuest->id,
                'quest_type' => $mstQuest->quest_type,
                'name' => $mstQuest->mst_quest_i18n?->name ?? '',
                'sort_order' => $mstQuest->sort_order,
                'asset_key' => $mstQuest->asset_key,
                'flavor_text' => $mstQuest->mst_quest_i18n?->flavor_text ?? '',
                'start_date' => $mstQuest->start_date,
                'end_date' => $mstQuest->end_date,
                'asset_image' => $mstQuest,
            ])
            ->schema([
                Fieldset::make('クエスト詳細')
                    ->schema([
                        TextEntry::make('id')->label('クエストID'),
                        TextEntry::make('quest_type')->label('クエストタイプ'),
                        TextEntry::make('name')->label('クエスト名'),
                        TextEntry::make('sort_order')->label('並び順'),
                        TextEntry::make('asset_key')->label('アセットキー'),
                        TextEntry::make('flavor_text')->label('フレーバーテキスト'),
                        TextEntry::make('start_date')->label('開始日'),
                        TextEntry::make('end_date')->label('終了日'),
                        AssetImageEntry::make('asset_image')->label('バナー'),
                    ]),
            ]);

        return $infolist;
    }

    public function table(Table $table): Table
    {
        $query = MstStage::query()
            ->with(['mst_stage_i18n', 'mst_in_game'])
            ->where('mst_quest_id', $this->questId)
            ->orderBy('sort_order');

        return $table
            ->query($query)
            ->searchable(false)
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ステージID'),
                Tables\Columns\TextColumn::make('mst_stage_i18n.name')
                    ->label('ステージ名'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('並び順'),
                Tables\Columns\TextColumn::make('cost_stamina')
                    ->label('消費スタミナ'),
                Tables\Columns\TextColumn::make('auto_lap')
                    ->label('スタミナブースト')
                    ->getStateUsing(fn ($record) => $record->getAutoLapLabel()),
                Tables\Columns\TextColumn::make('mst_in_game.bgm_asset_key')
                    ->label('BGMアセットキー'),
                Tables\Columns\TextColumn::make('mst_in_game.mst_enemy_outpost_id')
                    ->label('敵拠点ID'),
            ])
            ->actions([
                Action::make('detail')
                    ->label('詳細')
                    ->button()
                    ->url(function (MstStage $mstStage) {
                        return StageDetail::getUrl([
                            'stageId' => $mstStage->id,
                        ]);
                    }),
            ], position: ActionsPosition::BeforeColumns)
            ->description('クエストに紐づくステージ一覧');
    }

    public function eventInfolist(Infolist $infolist): Infolist
    {
        $mstQuest = $this->getMstModel();

        if ($mstQuest === null || $mstQuest->mst_event === null)
        {
            return $infolist;
        }

        $infolist
            ->state([
                'id'                        => $mstQuest->mst_event->id,
                'name'                      => $mstQuest->mst_event?->mst_event_i18n?->name ?? '',
                'mst_series_id'             => '[' . $mstQuest->mst_event->mst_series_id . ']' . ($mstQuest->mst_event?->mst_series?->mst_series_i18n?->name ?? ''),
                'asset_key'                 => $mstQuest->mst_event->asset_key,
                'is_displayed_series_logo'  => $mstQuest->mst_event->is_displayed_series_logo ? '表示あり' : '表示なし',
                'is_displayed_jump_plus'    => $mstQuest->mst_event->is_displayed_jump_plus ? '表示あり' : '表示なし',
                'start_at'                  => $mstQuest->mst_event->start_at,
                'end_at'                    => $mstQuest->mst_event->end_at,
                'release_key'               => $mstQuest->mst_event->release_key,
            ])
            ->schema([
                Fieldset::make('イベント情報')
                    ->schema([
                        TextEntry::make('id')->label('イベントID'),
                        TextEntry::make('name')->label('イベント名'),
                        TextEntry::make('mst_series_id')->label('シリーズID'),
                        TextEntry::make('asset_key')->label('アセットキー'),
                        TextEntry::make('is_displayed_series_logo')->label('作品ロゴの表示有無'),
                        TextEntry::make('is_displayed_jump_plus')->label('作品を読むボタンの表示有無'),
                        TextEntry::make('start_at')->label('開始日時'),
                        TextEntry::make('end_at')->label('終了日時'),
                        TextEntry::make('release_key')->label('リリースキー'),
                    ]),
            ]);

        return $infolist;
    }
}
