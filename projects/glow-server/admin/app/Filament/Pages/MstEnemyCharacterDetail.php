<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstEnemyCharacterResource;
use App\Infolists\Components\AssetImageEntry;
use App\Infolists\Components\LineBreakTextEntry;
use App\Models\Mst\MstEnemyCharacter;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class MstEnemyCharacterDetail extends MstDetailBasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.enemy-character-detail';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = '敵キャラ詳細';

    public string $mstEnemyCharacterId = '';

    protected $queryString = [
        'mstEnemyCharacterId',
    ];

    protected function getResourceClass(): ?string
    {
        return MstEnemyCharacterResource::class;
    }

    protected function getMstModelByQuery(): ?MstEnemyCharacter
    {
        return MstEnemyCharacter::query()
            ->with(['mst_enemy_character_i18n'])
            ->where('id', $this->mstEnemyCharacterId)
            ->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('mst_enemy_characters.id %s', $this->mstEnemyCharacterId);
    }

    protected function getSubTitle(): string
    {
        $mstEnemyCharacter = $this->getMstModel();
        return StringUtil::makeIdNameViewString(
            $mstEnemyCharacter->id,
            $mstEnemyCharacter->mst_enemy_character_i18n->name ?? '',
        );
    }

    public function infoList(): Infolist
    {
        $mstEnemyCharacter = $this->getMstModel();
        $i18n = $mstEnemyCharacter->mst_enemy_character_i18n;
        $state = [
            'id' => $mstEnemyCharacter->id,
            'name' => $i18n?->name ?? '',
            'description' => $i18n?->description ?? '',
            'mst_series_id' => $mstEnemyCharacter->mst_series_id,
            'asset_image' => $mstEnemyCharacter,
            'is_displayed_encyclopedia_label' => $mstEnemyCharacter->is_displayed_encyclopedia_label,
        ];
        $fieldset = Fieldset::make('基本情報')
            ->schema([
                TextEntry::make('id')->label('敵キャラID'),
                TextEntry::make('name')->label('敵キャラ名'),
                LineBreakTextEntry::make('description')->label('説明'),
                TextEntry::make('mst_series_id')->label('作品ID'),
                AssetImageEntry::make('asset_image')->label('敵キャラ画像'),
                TextEntry::make('is_displayed_encyclopedia_label')->label('図鑑表示'),
            ]);
        return $this->makeInfolist()
            ->state($state)
            ->schema([$fieldset]);
    }
}
