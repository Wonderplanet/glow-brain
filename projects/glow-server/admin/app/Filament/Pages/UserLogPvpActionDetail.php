<?php

namespace App\Filament\Pages;

use App\Constants\UserSearchTabs;
use App\Domain\Pvp\Enums\LogPvpResult;
use App\Filament\Pages\User\UserDataBasePage;
use App\Models\Log\LogPvpAction;
use App\Models\Mst\MstArtwork;
use App\Models\Mst\MstOutpostEnhancementI18n;
use App\Models\Mst\MstUnit;
use App\Models\Mst\MstUnitEncyclopediaEffect;
use App\Traits\LogInGameBattleTrait;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class UserLogPvpActionDetail extends UserDataBasePage
{
    use LogInGameBattleTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-log-pvp-action-detail';
    public string $currentTab = UserSearchTabs::LOG_PVP_ACTION->value;

    public string $logPvpActionId = '';

    private LogPvpAction $logPvpAction;

    protected $queryString = [
        'userId',
        'entry',
        'logPvpActionId',
    ];

    public function mount()
    {
        parent::mount();
        $this->breadcrumbList = array_merge($this->breadcrumbList, [
            self::getUrl(['userId' => $this->userId]) => $this->currentTab,
        ]);

        $this->logPvpAction = LogPvpAction::query()
            ->with([
                'sys_pvp_season',
                'opponent_user',
                'opponent_user_profile',
            ])
            ->where('id', $this->logPvpActionId)
            ->first();
    }

    public function infoList(): InfoList
    {
        $state = [
            // 基本情報
            'id' => $this->logPvpAction->id,
            'nginx_request_id' => $this->logPvpAction->nginx_request_id,
            'request_id' => $this->logPvpAction->request_id,
            'logging_no' => $this->logPvpAction->logging_no,
            'sys_pvp_session_id' => $this->logPvpAction->sys_pvp_season_id,
            'api_path' => $this->logPvpAction->api_path,
            'result' => LogPvpResult::tryFrom($this->logPvpAction->result)?->label() ?? ''
        ];

        return $this->makeInfolist()
            ->state($state)
            ->schema([
                Section::make('基本情報')
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('nginx_request_id')->label('NGINXリクエストID'),
                        TextEntry::make('request_id')->label('リクエストID'),
                        TextEntry::make('logging_no')->label('ログ番号'),
                        TextEntry::make('sys_pvp_session_id')->label('ランクマッチシーズンID'),
                        TextEntry::make('api_path')->label('APIパス'),
                        TextEntry::make('result')->label('ランクマッチ結果'),
                    ])
                    ->columns(),
            ]);
    }

    /**
     * LogPvpActionのPVPステータスJSONからパーティ情報表示用配列を生成
     *
     * @param string $pvpStatusJson
     * @return array<mixed>
     */
    private function generatePartyArrayFromPvpStatusJson(string $pvpStatusJson): array
    {
        $partyUnits = json_decode($pvpStatusJson, true)['unitStatuses'] ?? [];
        $mstUnits = MstUnit::query()
            ->with('mst_unit_i18n')
            ->whereIn('id', array_column($partyUnits, 'mstUnitId'))
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($partyUnits as $partyUnit) {
            $mstUnitId = $partyUnit['mstUnitId'] ?? '';
            $mstUnit = $mstUnits->get($mstUnitId);
            $result[] = [
                'mst_unit_id' => $mstUnitId,
                'name' => $mstUnit?->mst_unit_i18n?->name ?? '',
                'level' => $partyUnit['level'] ?? 0,
                'rank' => $partyUnit['rank'] ?? 0,
                'grade_level' => $partyUnit['gradeLevel'] ?? 0,
                'asset_path' => $mstUnit?->makeAssetPath() ?? '',
                'bg_path' => $mstUnit?->makeBgPath() ?? '',
            ];
        }
        return $result;
    }

    /**
     * LogPvpActionのPVPステータスJSONからゲート情報表示用配列を生成
     *
     * @param string $pvpStatusJson
     * @return array<mixed>
     */
    public function generateOpponentArrayFromPvpStatusJson(string $pvpStatusJson): array
    {
        $usrOutpostEnhancements = json_decode($pvpStatusJson, true)['usrOutpostEnhancements'] ?? [];

        $mstOutpostEnhancementI18ns = MstOutpostEnhancementI18n::query()
            ->whereIn('id', array_column($usrOutpostEnhancements, 'mst_outpost_enhancement_id'))
            ->get()
            ->keyBy('mst_outpost_enhancement_id');

        $outposts = [];
        foreach ($usrOutpostEnhancements as $usrOutpostEnhancement) {
            $mstOutpostEnhancementId = $usrOutpostEnhancement['mst_outpost_enhancement_id'];
            $outposts[] = [
                'mst_outpost_enhancement_id' => $mstOutpostEnhancementId,
                'level' => $usrOutpostEnhancement['level'],
                'name' => $mstOutpostEnhancementI18ns->get($mstOutpostEnhancementId)?->name ?? '',
            ];
        }
        return $outposts;
    }

    /**
     * LogPvpActionのPVPステータスJSONから原画情報表示用配列を生成
     *
     * @param string $pvpStatusJson
     * @return array<mixed>
     */
    public function generateArtworkArrayFromPvpStatusJson(string $pvpStatusJson): array
    {
        $mstArtworkIds = json_decode($pvpStatusJson, true)['mstArtworkIds'] ?? [];
        $mstArtworks = MstArtwork::query()
            ->with('mst_artwork_i18n')
            ->whereIn('id', $mstArtworkIds)
            ->get();

        $artworks = [];
        foreach ($mstArtworks as $mstArtwork) {
            $artworks[] = [
                'mst_artwork_id' => $mstArtwork->id,
                'name' => $mstArtwork->mst_artwork_i18n?->name ?? '',
                'asset_path' => $mstArtwork->makeAssetPath(),
                'bg_path' => $mstArtwork->makeBgPath(),
            ];
        }

        return $artworks;
    }

    /**
     * LogPvpActionのPVPステータスJSONから図鑑効果情報表示用配列を生成
     *
     * @param string $pvpStatusJson
     * @return array<mixed>
     */
    private function generateEncyclopediaEffectsFromPvpStatusJson(string $pvpStatusJson): array
    {
        $usrEncyclopediaEffects = json_decode($pvpStatusJson, true)['usrEncyclopediaEffects'] ?? [];
        $mstUnitEncyclopediaEffects = MstUnitEncyclopediaEffect::query()
            ->whereIn('id', array_column($usrEncyclopediaEffects, 'mstEncyclopediaEffectId'))
            ->get()
            ->keyBy('id');

        $effects = [];
        foreach ($mstUnitEncyclopediaEffects as $mstEncyclopediaEffect) {
            $effects[] = [
                'mst_encyclopedia_effect_id' => $mstEncyclopediaEffect->id,
                'effect_type' => $mstEncyclopediaEffect->effect_type,
                'value' => $mstEncyclopediaEffect->value,
            ];
        }

        return $effects;
    }

    public function userParty(): array
    {
        return $this->generatePartyArrayFromPvpStatusJson($this->logPvpAction->my_pvp_status);
    }

    public function userOutposts(): array
    {
        return $this->generateOpponentArrayFromPvpStatusJson($this->logPvpAction->my_pvp_status);
    }

    public function userArtworks(): array
    {
        return $this->generateArtworkArrayFromPvpStatusJson($this->logPvpAction->my_pvp_status);
    }

    public function userEncyclopediaEffects(): array
    {
        return $this->generateEncyclopediaEffectsFromPvpStatusJson($this->logPvpAction->my_pvp_status);
    }

    public function opponentParty(): array
    {
        return $this->generatePartyArrayFromPvpStatusJson($this->logPvpAction->opponent_pvp_status);
    }

    public function opponentOutposts(): array
    {
        return $this->generateOpponentArrayFromPvpStatusJson($this->logPvpAction->opponent_pvp_status);
    }

    public function opponentArtworks(): array
    {
        return $this->generateArtworkArrayFromPvpStatusJson($this->logPvpAction->opponent_pvp_status);
    }

    public function opponentEncyclopediaEffects(): array
    {
        return $this->generateEncyclopediaEffectsFromPvpStatusJson($this->logPvpAction->opponent_pvp_status);
    }
}
