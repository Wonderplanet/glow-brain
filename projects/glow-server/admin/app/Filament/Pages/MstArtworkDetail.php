<?php

namespace App\Filament\Pages;

use App\Constants\ImagePath;
use App\Constants\RarityType;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstArtworkResource;
use App\Infolists\Components\AssetBannerImageEntry;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstArtwork;
use App\Models\Mst\MstArtworkFragment;
use App\Tables\Columns\AssetImageColumn;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MstArtworkDetail extends MstDetailBasePage implements HasTable
{
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-artwork-detail';

    protected static ?string $title = '原画詳細';

    public string $mstArtworkId = '';

    protected $queryString = [
        'mstArtworkId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstArtworkResource::class;
    }

    protected function getMstModelByQuery(): ?MstArtwork
    {
        return MstArtwork::query()
            ->where('id', $this->mstArtworkId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('原画ID: %s', $this->mstArtworkId);
    }

    protected function getSubTitle(): string
    {
        $mstArtwork = $this->getMstModel();

        return StringUtil::makeIdNameViewString(
            $mstArtwork->id,
            $mstArtwork->getName(),
        );
    }

    public function table(Table $table): Table
    {
        $query = MstArtwork::query()
            ->with(
                'mst_artwork_i18n',
            );

        return $table
            ->query($query)
            ->searchable(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable(),
            ]);
    }

    public function infoList(): Infolist
    {
        $mstArtwork = $this->getMstModel();

        $mstArtworkI18n = $mstArtwork->mst_artwork_i18n;
        $mstSeriesI18n =$mstArtwork->mst_series->mst_series_i18n;

        $state = [
            'id'                    => $mstArtwork->id,
            'mst_series_id'         => '[' . $mstArtwork->mst_series_id . ']' . $mstSeriesI18n->name,
            'name'                  => $mstArtworkI18n->name,
            'outpost_additional_hp' => $mstArtwork->outpost_additional_hp,
            'release_key'           => $mstArtwork->release_key,
            'asset_image'           => $mstArtwork,
        ];
        $fieldset = Fieldset::make('原画詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('mst_series_id')->label('作品ID'),
                TextEntry::make('name')->label('原画名'),
                TextEntry::make('outpost_additional_hp')->label('完成時にゲートに加算するHP'),
                TextEntry::make('release_key')->label('リリースキー'),
                AssetImageEntry::make('asset_image')->label('原画画像')
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function artworkFragmentTable(): ?Table
    {
        $query = MstArtworkFragment::query()
            ->with([
                'mst_artwork_fragment_i18n',
                'mst_stage',
                'mst_stage.mst_stage_i18n',
                'mst_stage.mst_quests',
                'mst_stage.mst_quests.mst_quest_i18n',
            ])
            ->join('mst_artwork_fragment_positions', 'mst_artwork_fragments.id', '=', 'mst_artwork_fragment_positions.mst_artwork_fragment_id')
            ->where('mst_artwork_id', $this->mstArtworkId)
            ->orderBy('position');

        return $this->getTable()
            ->heading('原画のかけら情報')
            ->query($query)
            ->columns([
                TextColumn::make('mst_stage.mst_quests.mst_quest_i18n.name')->label('クエスト名'),
                TextColumn::make('mst_stage.mst_stage_i18n.name')->label('ステージ名'),
                TextColumn::make('drop_percentage')->label('ドロップ率')
                    ->getStateUsing(
                        function ($record) {
                            return $record->drop_percentage . '%';
                        }
                    ),
                TextColumn::make('mst_artwork_fragment_i18n.name')->label('名称'),
                AssetImageColumn::make('asset_image')->label('かけら画像'),
                TextColumn::make('position')->label('表示位置'),
            ])
            ->paginated(false);
    }

}
