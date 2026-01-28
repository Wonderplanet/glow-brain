<?php

namespace App\Filament\Pages;

use App\Constants\ImagePath;
use App\Constants\RarityType;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstEmblemResource;
use App\Infolists\Components\AssetImageEntry;
use App\Models\Mst\MstEmblem;
use App\Models\Mst\MstSeries;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class EmblemDetail extends MstDetailBasePage
{
    protected static string $view = 'filament.pages.emblem-detail';
    protected static ?string $title = 'エンブレム詳細';

    public string $mstEmblemId = '';

    protected $queryString = [
        'mstEmblemId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstEmblemResource::class;
    }

    protected function getMstModelByQuery(): ?MstEmblem
    {
        return MstEmblem::query()
            ->where('id', $this->mstEmblemId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('エンブレムID: %s', $this->mstEmblemId);
    }

    protected function getSubTitle(): string
    {
        $mstEmblem = $this->getMstModel();

        return StringUtil::makeIdNameViewString(
            $mstEmblem->id,
            $mstEmblem->getName(),
        );
    }

    public function infoList(): InfoList
    {
        $mstEmblem = $this->getMstModel();

        $mstEmblemI18n = $mstEmblem->mst_emblem_i18n;
        $mstSeries = MstSeries::query()
            ->with('mst_series_i18n')
            ->where('id', $mstEmblem->mst_series_id)
            ->first();

        $state = [
            'id'            => $mstEmblem->id,
            'name'          => $mstEmblemI18n->name,
            'emblem_type'   => $mstEmblem->emblem_type,
            'mst_series_id' => '[' . $mstEmblem->mst_series_id . '] ' . ($mstSeries->mst_series_i18n?->name ?? ''),
            'description'   => $mstEmblemI18n->description,
            'asset_key'     => $mstEmblem->asset_key,
            'release_key'   => $mstEmblem->release_key,
            'asset_image'   => $mstEmblem,
        ];
        $fieldset = Fieldset::make('エンブレム詳細')
            ->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('name')->label('エンブレム名称'),
                TextEntry::make('emblem_type')->label('エンブレムタイプ'),
                TextEntry::make('mst_series_id')->label('作品ID'),
                TextEntry::make('description')->label('フレーバーテキスト'),
                TextEntry::make('asset_key')->label('アセットキー'),
                TextEntry::make('release_key')->label('リリースキー'),
                AssetImageEntry::make('asset_image')->label('エンブレム画像'),
            ]);

        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }
}
