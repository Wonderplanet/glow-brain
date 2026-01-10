<?php

namespace App\Filament\Pages;

use App\Constants\PvpTab;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstDummyOutpost;
use App\Models\Mst\MstDummyUserUnit;
use App\Models\Mst\MstPvpDummy;
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

class MstPvpDummyDetail extends MstDetailBasePage implements HasTable
{
    use RewardInfoGetTrait;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static string $view = 'filament.pages.mst-pvp-dummy-detail';

    protected static ?string $title = 'ランクマッチダミープレイヤー詳細';

    public string $mstPvpDummyId = '';

    protected $queryString = [
        'mstPvpDummyId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstPvpDummies::class;
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstPvp = $this->getMstModel();
        if ($mstPvp === null) {
            return [];
        }

        return [
            MstPvpDummies::getUrl() => PvpTab::PVP_DUMMY,
        ];
    }

    protected function getMstModelByQuery(): ?MstPvpDummy
    {
        return MstPvpDummy::query()->where('id', $this->mstPvpDummyId)->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('ランクマッチダミープレイヤーID: %s', $this->mstPvpDummyId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->mstPvpDummyId,
            $this->getMstModel()?->mst_dummy_user?->mst_dummy_user_i18n?->name ?? '',
        );
    }

    public function table(Table $table): Table
    {
        $query = MstPvpDummy::query();

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
        $mstPvpDummy = $this->getMstModel();

        $state = [
            'id' => $mstPvpDummy->id,
            'name' => $mstPvpDummy->mst_dummy_user?->mst_dummy_user_i18n?->name,
            'chara_image'   => $mstPvpDummy->mst_dummy_user->mst_unit,
            'emblem_image'   => $mstPvpDummy->mst_dummy_user->mst_emblem,
            'score' => $mstPvpDummy->score,
            'release_key' => $mstPvpDummy->release_key,
        ];
        $fieldset = Fieldset::make('ランクマッチ詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name')->label('ダミーユーザー名'),
                AssetImageEntry::make('chara_image')->label('キャラ画像'),
                AssetImageEntry::make('emblem_image')->label('エンブレム画像'),
                TextEntry::make('score')->label('スコア'),
                TextEntry::make('release_key')->label('リリースキー'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }

    public function dummyUserUnits(): array
    {
        $mstDummyUserId = $this->getMstModel()->mst_dummy_user->id;

        $mstDummyUserUnits = MstDummyUserUnit::query()
            ->with([
                'mst_unit',
                'mst_unit.mst_unit_i18n',
            ])
            ->where('mst_dummy_user_id', $mstDummyUserId)
            ->get();

        $result = [];
        foreach ($mstDummyUserUnits as $mstDummyUserUnit) {
            $result[] = [
                'mst_unit_id' => $mstDummyUserUnit->mst_unit_id,
                'name' => $mstDummyUserUnit->mst_unit->mst_unit_i18n->name ?? '',
                'asset_path' => $mstDummyUserUnit->mst_unit->makeAssetPath(),
                'bg_path' => $mstDummyUserUnit->mst_unit->makeBgPath(),
                'level' => $mstDummyUserUnit->level,
                'rank' => $mstDummyUserUnit->rank,
                'grade_level' => $mstDummyUserUnit->grade_level,
            ];
        }

        return $result;
    }

    public function dummyOutposts(): array
    {
        $mstPvpDummy = $this->getMstModel();

        $mstDummyOutposts = MstDummyOutpost::query()
            ->where('mst_dummy_user_id', $mstPvpDummy->mst_dummy_user->id)
            ->with([
                'mst_outpost_enhancement',
                'mst_outpost_enhancement.mst_outpost_enhancement_i18n',
                'mst_outpost_enhancement.mst_outpost_enhancement_level',
                'mst_outpost_enhancement.mst_outpost_enhancement_level.mst_outpost_enhancement_level_i18n',
            ])
            ->get();

        $result = [];
        foreach ($mstDummyOutposts as $mstDummyOutpost) {
            $mstOutpostEnhancement = $mstDummyOutpost->mst_outpost_enhancement;
            $result[] = [
                'id' => $mstOutpostEnhancement->id,
                'name' => $mstOutpostEnhancement->mst_outpost_enhancement_i18n->name ?? '',
                'level' => $mstDummyOutpost->level,
                'description' => $mstOutpostEnhancement
                    ->mst_outpost_enhancement_level
                    ->filter(function ($level) use ($mstDummyOutpost) {
                        return $level->level == $mstDummyOutpost->level;
                    })
                    ->first()
                    ?->mst_outpost_enhancement_level_i18n
                    ?->description ?? '',
            ];
        }

        return $result;
    }
}

