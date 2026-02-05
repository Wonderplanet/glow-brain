<?php

namespace App\Filament\Pages;

use App\Constants\AttackElementDamageType;
use App\Constants\InGameContentType;
use App\Constants\PartyRuleType;
use App\Constants\QuestType;
use App\Constants\ResetType;
use App\Constants\StageEventType;
use App\Constants\StageRuleType;
use App\Domain\Resource\Mst\Models\MstStageClearTimeReward;
use App\Domain\Resource\Mst\Models\MstStageEnhanceRewardParam;
use App\Domain\Resource\Mst\Models\MstStageEventSetting;
use App\Domain\Unit\Enums\AttackKind;
use App\Filament\Pages\Mst\MstDetailBasePage;
use App\Filament\Resources\MstQuestResource;
use App\Infolists\Components\AssetBgmEntry;
use App\Infolists\Components\AssetImageEntry;
use App\Infolists\Components\AssetKomaImageEntry;
use App\Models\Mst\MstInGameSpecialRule;
use App\Models\Mst\MstStage;
use App\Models\Mst\MstStageEventReward;
use App\Models\Mst\MstStageReward;
use App\Services\Reward\RewardInfoGetHandleService;
use App\Utils\StringUtil;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Collection;

class StageDetail extends MstDetailBasePage
{
    protected static string $view = 'filament.pages.stage-detail';

    protected static ?string $title = 'ステージ詳細';

    public string $stageId = '';

    protected $queryString = [
        'stageId',
    ];

    protected ?MstStageEventSetting $mstStageEventSetting;

    protected Collection $rewardInfos;

    // 依存関係
    private RewardInfoGetHandleService $rewardInfoGetHandleService;

    public function mount()
    {
        parent::mount();

        $this->rewardInfoGetHandleService = app(RewardInfoGetHandleService::class);

        $this->setMstStageEventSetting();
        $this->createRewardInfos();
    }

    protected function getResourceClass(): ?string
    {
        return MstQuestResource::class;
    }

    protected function getMstModelByQuery(): ?MstStage
    {
        return MstStage::query()->where('id', $this->stageId)?->first();
    }

    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return sprintf('ステージID: %s', $this->stageId);
    }

    protected function getSubTitle(): string
    {
        return StringUtil::makeIdNameViewString(
            $this->stageId,
            $this->getMstModel()->mst_stage_i18n->name,
        );
    }

    protected function getAdditionalBreadcrumbs(): array
    {
        $mstStage = $this->getMstModel();
        if ($mstStage === null) {
            return [];
        }

        return [
            QuestDetail::getUrl(['questId' => $this->getMstModel()->mst_quest_id]) => QuestDetail::getMainTitle(),
        ];
    }

    private function setMstStageEventSetting()
    {
        $this->mstStageEventSetting = MstStageEventSetting::query()
            ->where('mst_stage_id', $this->stageId)
            ->first();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // stageデータ
        $mstStage = $this->getMstModel();
        $mstQuest = $mstStage->mst_quests()->first();
        // Ingameデータ
        $mstIngame = $mstStage->mst_in_game()->first();
        // 設定されたコマ
        $mstKomaLine = $mstIngame->mst_koma_line()->first();
        // 敵拠点情報
        $mstEnemyOutposts = $mstIngame->mst_enemy_outpost()->first();
        // 原画のかけら情報（ドロップ)
        $mstArtworkFragment = $mstStage->mst_artwork_fragment()->first();
        // ステージ内のシーケンス情報
        $mstAutoPlayerSequence = $mstIngame->mst_auto_player_sequence()->first();

        $mstAttack = null;
        $mstAttackElements = null;
        $mstEnemyCharacter = null;
        $mstEnemyStageParameter = null;
        $mstUnitAbility = null;
        if ($mstAutoPlayerSequence !== null) {
            $mstAttack = $mstAutoPlayerSequence->mst_attack()->first();
            $mstEnemyCharacter = $mstAutoPlayerSequence->mst_enemy_character()->first();
        }
        if ($mstEnemyCharacter !== null) {
            $mstEnemyStageParameter = $mstEnemyCharacter->mst_enemy_stage_parameter()->first();
        }
        if ($mstAttack !== null) {
            $mstAttackElements = $mstAttack->mst_attack_elements()->get();
        }
        if ($mstEnemyStageParameter !== null) {
            $mstUnitAbility = $mstEnemyStageParameter->mst_unit_abilities()->first();
        }

        if ($mstStage === null) {
            return $infolist;
        }

        // 攻撃係数
        $enemyAttackCoef = 1.0;
        if ($mstAutoPlayerSequence && $mstEnemyStageParameter !== null) {
            if ($mstEnemyStageParameter->character_unit_kind == 'Normal') {
                $enemyAttackCoef = $mstAutoPlayerSequence->enemy_attack_coef * $mstIngame->normal_enemy_attack_coef;

            } elseif ($mstEnemyStageParameter->character_unit_kind == 'Normal') {
                $enemyAttackCoef = $mstAutoPlayerSequence->enemy_attack_coef * $mstIngame->boss_enemy_attack_coef;
            }
        }
        // ワザ備考用の改行対応
        $attack_remarks = '';
        $attack_range = '';
        if ($mstAttackElements !== null && $mstAttackElements->count() > 0) {
            foreach ($mstAttackElements as $mstAttackElement) {
                // 通常攻撃のhit_typeとeffect_typeを取得
                if ($mstAttackElement->hit_type !== null && $mstAttackElement->effect_type !== null) {
                    $attack_remarks .= '[hit_type]' . $mstAttackElement->hit_type;
                    $attack_remarks .= ' [effect_type]' . $mstAttackElement->effect_type;
                    $attack_remarks .= '<br />';
                }

                $attack_range .= ' [range_start_type]' . $mstAttackElement->range_start_type;
                $attack_range .= ' [range_end_type]' . $mstAttackElement->range_end_type;
                $attack_range .= ' [range_start_parameter]' . $mstAttackElement->range_start_parameter;
                $attack_range .= ' [range_end_parameter]' . $mstAttackElement->range_end_parameter;
                $attack_range .= '<br />';
            }
        }

        $infolist
            ->state([
                'mst_quest_id' => $mstStage->mst_quest_id,
                'quest_type' => $mstQuest->quest_type,
                'stage_number' => $mstStage->stage_number,
                'prev_mst_stage_id' => $mstStage->prev_mst_stage_id,
                'recommended_level' => $mstStage->recommended_level,
                'cost_stamina' => $mstStage->cost_stamina,
                'exp' => $mstStage->exp,
                'coin' => $mstStage->coin,
                'auto_lap_type' => $mstStage->getAutoLapTypeLabel(),
                'max_auto_lap_count' => $mstStage->max_auto_lap_count,
                'reward_amount' => $mstStage->reward_count,
                'bgm_asset_key' => $mstIngame->bgm_asset_key,
                'bgm_asset' => $mstIngame->makeAssetPath(),
                'mst_enemy_outpost_id' => $mstIngame->mst_enemy_outpost_id,
                'enemy_outpost_hp' => $mstEnemyOutposts->hp,
                'artwork_fragment_id' => $mstArtworkFragment->mst_artwork_id ?? 0,
                'artwork_fragment_drop_percentage' => $mstArtworkFragment->drop_percentage ?? 0,
                'artwork_asset_image' => $mstArtworkFragment,

                'koma_id' => $mstKomaLine->id,
                'koma_count' => $mstKomaLine->getKomaCount(),
                'height' => $mstKomaLine->height,
                'koma1_width' => $mstKomaLine->koma1_width,
                'koma2_width' => $mstKomaLine->koma2_width,
                'koma3_width' => $mstKomaLine->koma3_width,
                'koma4_width' => $mstKomaLine->koma4_width,
                'koma1_back_ground_offset' => $mstKomaLine->koma1_back_ground_offset,
                'koma1_effect_type' => $mstKomaLine->koma1_effect_type,
                'koma1_effect_parameter1' => $mstKomaLine->koma1_effect_parameter1,
                'koma1_effect_parameter2' => $mstKomaLine->koma1_effect_parameter2,
                'koma2_back_ground_offset' => $mstKomaLine->koma2_back_ground_offset,
                'koma2_effect_type' => $mstKomaLine->koma2_effect_type,
                'koma2_effect_parameter1' => $mstKomaLine->koma2_effect_parameter1,
                'koma2_effect_parameter2' => $mstKomaLine->koma2_effect_parameter2,
                'koma3_back_ground_offset' => $mstKomaLine->koma3_back_ground_offset,
                'koma3_effect_type' => $mstKomaLine->koma3_effect_type,
                'koma3_effect_parameter1' => $mstKomaLine->koma3_effect_parameter1,
                'koma3_effect_parameter2' => $mstKomaLine->koma3_effect_parameter2,
                'koma4_back_ground_offset' => $mstKomaLine->koma4_back_ground_offset,
                'koma4_effect_type' => $mstKomaLine->koma4_effect_type,
                'koma4_effect_parameter1' => $mstKomaLine->koma4_effect_parameter1,
                'koma4_effect_parameter2' => $mstKomaLine->koma4_effect_parameter2,
                'koma1_asset_key' => $mstKomaLine->koma1_asset_key,
                'koma2_asset_key' => $mstKomaLine->koma2_asset_key,
                'koma3_asset_key' => $mstKomaLine->koma3_asset_key,
                'koma4_asset_key' => $mstKomaLine->koma4_asset_key,
                'koma1_asset' => $mstKomaLine->makeAssetPathByKey("koma1_asset_key"),
                'koma2_asset' => $mstKomaLine->makeAssetPathByKey("koma2_asset_key"),
                'koma3_asset' => $mstKomaLine->makeAssetPathByKey("koma3_asset_key"),
                'koma4_asset' => $mstKomaLine->makeAssetPathByKey("koma4_asset_key"),

                'sequence_group_id' => $mstAutoPlayerSequence->sequence_group_id ?? '-',
                'sequence_element_id' => $mstAutoPlayerSequence->sequence_element_id ?? '-',
                'enemy_id' => $mstEnemyCharacter->id ?? '-',
                'unit_kind' => $mstEnemyStageParameter->character_unit_kind ?? '-',
                'color' => $mstEnemyStageParameter->color ?? '-',
                'role_type' => $mstEnemyStageParameter->role_type ?? '-',
                'enemy_asset_key' => $mstEnemyCharacter->asset_key ?? '',
                'enemy_name' => $mstEnemyCharacter->mst_enemy_character_i18n->name ?? '',
                'enemy_character' => $mstEnemyCharacter ?? collect(),
                'aura_type' => $mstAutoPlayerSequence->aura_type ?? '-',
                'death_type' => $mstAutoPlayerSequence->death_type ?? '-',
                'enemy_hp' => $mstEnemyStageParameter->hp ?? 0,
                'enemy_hp_coef' => $mstAutoPlayerSequence->enemy_hp_coef ?? 0,
                'attack_power' => $mstEnemyStageParameter->attack_power ?? 0,
                'enemy_attack_coef' => $enemyAttackCoef ?? '-',
                'move_speed' => $mstEnemyStageParameter->move_speed ?? 0,
                'well_distance' => $mstEnemyStageParameter->well_distance ?? 0,
                'move_start_condition_type' => $mstAutoPlayerSequence->move_start_condition_type ?? '-',
                'move_start_condition_value' => $mstAutoPlayerSequence->move_start_condition_value ?? '-',
                'move_stop_condition_type' => $mstAutoPlayerSequence->move_stop_condition_type ?? '-',
                'move_stop_condition_value' => $mstAutoPlayerSequence->move_stop_condition_value ?? '-',
                'damage_knock_back_count' => $mstEnemyStageParameter->damage_knock_back_count ?? 0,
                'defeated_score' => $mstAutoPlayerSequence->defeated_score ?? 0,
                'override_drop_battle_point' => $mstAutoPlayerSequence->override_drop_battle_point ?? 0,
                'transformation_condition_value' => $mstEnemyStageParameter->transformation_condition_value ?? 0,
                'transformation_condition_type' => $mstEnemyStageParameter->transformation_condition_type ?? '',
                'condition_type' => $mstAutoPlayerSequence->condition_type ?? '',
                'condition_value' => $mstAutoPlayerSequence->condition_value ?? '',
                'summon_count' => $mstAutoPlayerSequence->summon_count ?? 0,
                'action_delay' => $mstAutoPlayerSequence->action_delay ?? 0,
                'summon_animation_type' => $mstAutoPlayerSequence->summon_animation_type ?? '',
                'summon_position' => $mstAutoPlayerSequence->summon_position ?? '',
                'move_stop_condition_type' => $mstAutoPlayerSequence->move_stop_condition_type ?? '',
                'move_restart_condition_type' => $mstAutoPlayerSequence->move_restart_condition_type ?? '',
                'move_restart_condition_value' => $mstAutoPlayerSequence->move_restart_condition_value ?? '',
                'move_loop_count' => $mstAutoPlayerSequence->move_loop_count ?? 0,
                'is_damage_invalidation' => $mstEnemyOutposts->is_damage_invalidation ?? false,
                'deactivation_condition_type' => $mstAutoPlayerSequence->deactivation_condition_type ?? '',
                'deactivation_condition_value' => $mstAutoPlayerSequence->deactivation_condition_value ?? '',
                'is_attack_special' =>
                    ($mstAttack !== null && $mstAttack->attack_kind === AttackKind::SPECIAL->value) ? 'あり' : 'なし',
                'normal_attack_action_frames' =>
                    ($mstAttack !== null && $mstAttack->attack_kind === AttackKind::NORMAL->value) ? $mstAttack->action_frames : 'なし',
                'normal_attack_next_attack_interval' =>
                    ($mstAttack !== null && $mstAttack->attack_kind === AttackKind::NORMAL->value) ? $mstAttack->next_attack_interval : 'なし',
                // 攻撃力(attack_power) * 攻撃係数(enemy_attack_coef * boss_enemy_attack_coef) とのこと
                'normal_attack_dps' => ($mstEnemyStageParameter !== null) ? $mstEnemyStageParameter->attack_power * $enemyAttackCoef : 0,
                // 備考（通常攻撃のattack_elementのhit_typeとeffect_type）
                'normal_attack_remarks' => $attack_remarks ?? '-',
                'special_frames_sec' =>
                    ($mstAttack !== null && $mstAttack->attack_kind === AttackKind::SPECIAL->value) ? $mstAttack->action_frames : 'なし',
                'special_attack_combo_cycle' => $mstEnemyStageParameter->attack_combo_cycle ?? 0,
                'unit_ability' => $mstUnitAbility->mst_ability_id ?? 'なし',
                'attack_info_detail' =>
                    ($mstAttackElements !== null) ? AttackElementDamageType::labels()->toArray()[$mstAttackElements->first()->damage_type] : 'なし',
                'attack_range' => $attack_range ?? 'なし',
            ])
            ->schema([
                Fieldset::make('ステージ詳細')
                    ->schema([
                        TextEntry::make('mst_quest_id')->label('クエストID'),
                        TextEntry::make('quest_type')->label('クエストタイプ'),
                        TextEntry::make('stage_number')->label('ステージ番号'),
                        TextEntry::make('recommended_level')->label('推奨レベル'),
                        TextEntry::make('prev_mst_stage_id')->label('前ステージID'),
                        TextEntry::make('cost_stamina')->label('消費スタミナ'),
                        TextEntry::make('auto_lap_type')->label('スタミナブーストタイプ'),
                        TextEntry::make('max_auto_lap_count')->label('スタミナブースト最大周回数'),
                        TextEntry::make('mst_enemy_outpost_id')->label('敵拠点ID'),
                        TextEntry::make('enemy_outpost_hp')->label('敵拠点HP'),
                        TextEntry::make('bgm_asset_key')->label('BGMアセットキー'),
                        AssetBgmEntry::make('bgm_asset')->label('BGM'),

                    ]),
                Fieldset::make('ステージ基本報酬')
                    ->schema([
                        TextEntry::make('exp')->label('経験値'),
                        TextEntry::make('coin')->label('コイン'),
                        TextEntry::make('reward_amount')->label('報酬数'),
                        TextEntry::make('artwork_fragment_id')->label('ドロップする原画のかけらID'),
                        TextEntry::make('artwork_fragment_drop_percentage')->label('原画のかけらドロップ率'),
                        AssetImageEntry::make('artwork_asset_image')->label('原画画像')
                    ]),
                Fieldset::make('Enemyシーケンス')
                    ->schema([
                        TextEntry::make('enemy_name')->label('ファントム名'),
                        AssetImageEntry::make('enemy_character')->label('ファントム画像'),
                        TextEntry::make('enemy_asset_key')->label('ファントムアセットキー'),
                        TextEntry::make('sequence_group_id')->label('シーケンスグループID'),
                        TextEntry::make('sequence_element_id')->label('シーケンスエレメントID'),
                        TextEntry::make('enemy_id')->label('敵ID'),
                        TextEntry::make('unit_kind')->label('unit_kind'),
                        TextEntry::make('color')->label('属性'),
                        TextEntry::make('role_type')->label('ロールタイプ'),
                        TextEntry::make('aura_type')->label('オーラタイプ'),
                        TextEntry::make('death_type')->label('デスエフェクトタイプ'),
                        TextEntry::make('is_attack_special')->label('必殺ワザ有無'),
                        TextEntry::make('special_frames_sec')->label('必殺技F(秒)'),
                        TextEntry::make('special_attack_combo_cycle')->label('必殺発動回数'),
                        TextEntry::make('normal_attack_action_frames')->label('通常攻撃F'),
                        TextEntry::make('normal_attack_next_attack_interval')->label('通常攻撃CDF'),
                        TextEntry::make('normal_attack_dps')->label('通常攻撃DPS'),
                        TextEntry::make('normal_attack_remarks')->label('通常攻撃備考')
                            ->formatStateUsing(fn (string $state) => nl2br($state))
                            ->html(),
                        TextEntry::make('attack_range')->label('攻撃範囲')
                            ->formatStateUsing(fn (string $state) => nl2br($state))
                            ->html(),
                        TextEntry::make('attack_info_detail')->label('攻撃情報詳細'),
                        TextEntry::make('unit_ability')->label('特性'),
                        TextEntry::make('enemy_hp')->label('HP'),
                        TextEntry::make('enemy_hp_coef')->label('HP係数'),
                        TextEntry::make('attack_power')->label('攻撃力'),
                        TextEntry::make('enemy_attack_coef')->label('攻撃力係数'),
                        TextEntry::make('move_speed')->label('移動速度'),
                        TextEntry::make('well_distance')->label('well_distance'),
                        TextEntry::make('move_start_condition_type')->label('移動開始条件タイプ'),
                        TextEntry::make('move_start_condition_value')->label('移動開始条件値'),
                        TextEntry::make('move_stop_condition_type')->label('移動停止条件タイプ'),
                        TextEntry::make('move_stop_condition_value')->label('移動停止条件値'),
                        TextEntry::make('damage_knock_back_count')->label('ダメージノックバック回数'),
                        TextEntry::make('defeated_score')->label('撃破スコア'),
                        TextEntry::make('override_drop_battle_point')->label('上書き撃破スコア'),
                        TextEntry::make('transformation_condition_value')->label('変身先ID'),
                        TextEntry::make('transformation_condition_type')->label('変身条件タイプ'),
                        TextEntry::make('condition_type')->label('出撃条件'),
                        TextEntry::make('condition_value')->label('出撃値'),
                        TextEntry::make('summon_count')->label('召喚回数'),
                        TextEntry::make('action_delay')->label('アクション遅延'),
                        TextEntry::make('summon_animation_type')->label('出撃(アニメーション)'),
                        TextEntry::make('summon_position')->label('召喚(possummon_position)'),
                        TextEntry::make('move_restart_condition_type')->label('移動再スタート条件'),
                        TextEntry::make('move_restart_condition_value')->label('移動再スタート条件値'),
                        TextEntry::make('move_loop_count')->label('再スタート回数'),
                        TextEntry::make('is_damage_invalidation')->label('ゲートダメージ無効化'),
                        TextEntry::make('deactivation_condition_type')->label('出現停止条件'),
                        TextEntry::make('deactivation_condition_value')->label('出現停止条件値'),
                    ]),
                Fieldset::make('コマ設計')
                    ->schema([
                        TextEntry::make('koma_id')->label('コマ行ID'),
                        TextEntry::make('koma_count')->label('コマ数'),
                        TextEntry::make('height')->label('コマ高さ設定'),
                        TextEntry::make('koma1_width')->label('コマ1幅設定'),
                        TextEntry::make('koma2_width')->label('コマ2幅設定'),
                        TextEntry::make('koma3_width')->label('コマ3幅設定'),
                        TextEntry::make('koma4_width')->label('コマ4幅設定'),
                        TextEntry::make('koma1_back_ground_offset')->label('コマ1背景オフセット'),
                        TextEntry::make('koma1_effect_type')->label('コマ1エフェクトタイプ'),
                        TextEntry::make('koma1_effect_parameter1')->label('コマ1エフェクトパラメータ1'),
                        TextEntry::make('koma1_effect_parameter2')->label('コマ1エフェクトパラメータ2'),
                        TextEntry::make('koma2_back_ground_offset')->label('コマ2背景オフセット'),
                        TextEntry::make('koma2_effect_type')->label('コマ2エフェクトタイプ'),
                        TextEntry::make('koma2_effect_parameter1')->label('コマ2エフェクトパラメータ1'),
                        TextEntry::make('koma2_effect_parameter2')->label('コマ2エフェクトパラメータ2'),
                        TextEntry::make('koma3_back_ground_offset')->label('コマ3背景オフセット'),
                        TextEntry::make('koma3_effect_type')->label('コマ3エフェクトタイプ'),
                        TextEntry::make('koma3_effect_parameter1')->label('コマ3エフェクトパラメータ1'),
                        TextEntry::make('koma3_effect_parameter2')->label('コマ3エフェクトパラメータ2'),
                        TextEntry::make('koma4_back_ground_offset')->label('コマ4背景オフセット'),
                        TextEntry::make('koma4_effect_type')->label('コマ4エフェクトタイプ'),
                        TextEntry::make('koma4_effect_parameter1')->label('コマ4エフェクトパラメータ1'),
                        TextEntry::make('koma4_effect_parameter2')->label('コマ4エフェクトパラメータ2'),
                        TextEntry::make('koma1_asset_key')->label('コマアセットキー１'),
                        TextEntry::make('koma2_asset_key')->label('コマアセットキー２'),
                        TextEntry::make('koma3_asset_key')->label('コマアセットキー３'),
                        TextEntry::make('koma4_asset_key')->label('コマアセットキー４'),
                        AssetKomaImageEntry::make('koma1_asset')->label('コマ１'),
                        AssetKomaImageEntry::make('koma2_asset')->label('コマ２'),
                        AssetKomaImageEntry::make('koma3_asset')->label('コマ３'),
                        AssetKomaImageEntry::make('koma4_asset')->label('コマ4'),
                    ])
            ]);

        return $infolist;
    }

    private function createRewardInfos(): void
    {
        $mstStage = $this->getMstModel();
        if ($mstStage === null) {
            return;
        }

        if ($mstStage->mst_quests->quest_type === QuestType::EVENT->value) {
            $mstStageRewards = $mstStage->mst_stage_event_rewards;
            $rewardDtos = $mstStageRewards->map(function (MstStageEventReward $mstStageEventReward) {
                return $mstStageEventReward->reward;
            });
        } else {
            $mstStageRewards = $mstStage->mst_stage_rewards;
            $rewardDtos = $mstStageRewards->map(function (MstStageReward $mstStageReward) {
                return $mstStageReward->reward;
            });
        }

        $this->rewardInfos = $this->rewardInfoGetHandleService->build($rewardDtos)->getRewardInfos();
    }

    public function getFirstClearRewardTableRows(): array
    {
        $mstStage = $this->getMstModel();
        if ($mstStage === null) {
            return [];
        }

        $rows = [];

        if ($mstStage->mst_quests->quest_type === QuestType::EVENT->value) {
            $mstStageRewards = $mstStage->mst_stage_event_rewards
                ->filter(function (MstStageEventReward $mstStageEventReward) {
                    return $mstStageEventReward->isFirstClearReward();
                });
        } else {
            $mstStageRewards = $mstStage->mst_stage_rewards
            ->filter(function (MstStageReward $mstStageReward) {
                return $mstStageReward->isFirstClearReward();
            });
        }

        $rewardInfos = $this->rewardInfos;

        foreach ($mstStageRewards as $mstStageReward) {
            /** @var MstStageReward $mstStageReward */

            $rewardInfoId = $mstStageReward->reward->getId();
            /** @var \App\Entities\RewardInfo $rewardInfo */
            $rewardInfo = $rewardInfos->get($rewardInfoId);
            if ($rewardInfo === null) {
                continue;
            }

            $rows[] = [
                'ID' => $mstStageReward->id,
                '報酬情報' => $rewardInfo,
            ];
        }

        return $rows;
    }

    public function getAlwaysRewardTableRows(): array
    {
        $mstStage = $this->getMstModel();
        if ($mstStage === null) {
            return [];
        }

        $rows = [];

        if ($mstStage->mst_quests->quest_type === QuestType::EVENT->value) {
            $mstStageRewards = $mstStage->mst_stage_event_rewards
                ->filter(function (MstStageEventReward $mstStageEventReward) {
                    return $mstStageEventReward->isAlwaysReward();
                });
        } else {
            $mstStageRewards = $mstStage->mst_stage_rewards
                ->filter(function (MstStageReward $mstStageRewardGroup) {
                    return $mstStageRewardGroup->isAlwaysReward();
                });
            $totalWeight = $mstStageRewards->sum(function (MstStageReward $mstStageRewardGroup) {
                return $mstStageRewardGroup->weight;
            });
        }

        $rewardInfos = $this->rewardInfos;

        foreach ($mstStageRewards as $mstStageReward) {
            /** @var MstStageReward $mstStageReward */

            $rewardInfoId = $mstStageReward->reward->getId();
            /** @var \App\Entities\RewardInfo $rewardInfo */
            $rewardInfo = $rewardInfos->get($rewardInfoId);
            if ($rewardInfo === null) {
                continue;
            }

            if ($mstStage->mst_quests->quest_type === QuestType::EVENT->value) {
                $probability = sprintf('%.4f%%', $mstStageReward->percentage);
            } else {
                if (!$totalWeight) {
                    $probability = '0.0000%';
                } else {
                    $probability = sprintf('%.4f%%', $mstStageReward->weight / $totalWeight * 100);
                }
            }
            $rows[] = [
                'ID' => $mstStageReward->id,
                '報酬情報' => $rewardInfo,
                '抽選重み' => $mstStageReward->weight,
                '抽選確率' => $probability,
            ];
        }
        return $rows;
    }

    public function getInGameSpecialRuleRows(): array
    {
        $mstInGameSpecialRules = MstInGameSpecialRule::query()
            ->where('target_id', $this->getMstModel()->id)
            ->where('content_type', InGameContentType::STAGE->value)
            ->orderBy('rule_type')
            ->get()
            ->map(function (MstInGameSpecialRule $model) {
                $model->rule_type = PartyRuleType::tryFrom($model->rule_type)?->label() ??
                    StageRuleType::tryFrom($model->rule_type)?->label() ??
                    $model->rule_type;
                return $model;
            })
            ->toArray();
        $mstInGameSpecialRuleRows = [];
        foreach ($mstInGameSpecialRules as $mstInGameSpecialRule) {
            $mstInGameSpecialRuleRows[] = [
                'ID' => $mstInGameSpecialRule['id'],
                'ルール条件' => $mstInGameSpecialRule['rule_type'],
                'ルール条件値' => $mstInGameSpecialRule['rule_value'],
            ];
        }
        return $mstInGameSpecialRuleRows;
    }

    public function eventSettinglist(Infolist $infolist): Infolist
    {
        if ($this->mstStageEventSetting === null) {
            return $infolist;
        }

        $resetType = ResetType::tryFrom($this->mstStageEventSetting->reset_type);

        $infolist
            ->state([
                'id'                      => $this->mstStageEventSetting->id,
                'reset_type'              => $resetType ? $resetType->label() : 'リセットなし',
                'clearable_count'         => $this->mstStageEventSetting->clearable_count . '回',
                'ad_challenge_count'      => $this->mstStageEventSetting->ad_challenge_count . '回',
                'mst_stage_rule_group_id' => $this->mstStageEventSetting->mst_stage_rule_group_id ?? '無し',
                'start_at'                => $this->mstStageEventSetting->start_at,
                'end_at'                  => $this->mstStageEventSetting->end_at,
                'release_key'             => $this->mstStageEventSetting->release_key,
                'stage_event_type' => $this->mstStageEventSetting->stage_event_type
            ])
            ->schema([
                Fieldset::make('イベントステージ設定詳細')
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('reset_type')->label('リセットタイプ'),
                        TextEntry::make('stage_event_type')->label('ステージタイプ'),
                        TextEntry::make('mst_stage_rule_group_id')->label('ルール'),
                        TextEntry::make('clearable_count')->label('クリア可能回数'),
                        TextEntry::make('ad_challenge_count')->label('広告視聴で挑戦できる回数'),
                        TextEntry::make('start_at')->label('開始日時'),
                        TextEntry::make('end_at')->label('終了日時'),
                        TextEntry::make('release_key')->label('リリースキー'),
                    ]),
            ]);

        return $infolist;
    }

    public function getEnhanceRewardParams(): array
    {
        $mstStage = $this->getMstModel();

        if ($mstStage->mst_quests->quest_type !== QuestType::ENHANCE->value) {
            return [];
        }

        $mstStageEnhanceRewardParams = MstStageEnhanceRewardParam::query()
            ->orderBy('min_threshold_score', 'asc')
            ->get();

        $data = [];
        foreach ($mstStageEnhanceRewardParams as $mstStageEnhanceRewardParam) {

            $data[] = [
                'ID' => $mstStageEnhanceRewardParam['id'],
                '乗数が適用されるスコアの下限値' => $mstStageEnhanceRewardParam['min_threshold_score'],
                '報酬量' => $mstStageEnhanceRewardParam['coin_reward_amount']
            ];
        }
        return $data;
    }

    public function getMstClearTimeRewardTableRows(): array
    {
        $mstStage = $this->getMstModel();
        if ($mstStage === null) {
            return [];
        }

        if ($this->mstStageEventSetting === null || $this->mstStageEventSetting->stage_event_type !== StageEventType::SPEED_ATTACK->value) {
            return [];
        }

        $mstStageClearTimeRewards = $mstStage->mst_stage_clear_time_rewards;

        $rewardDtos = $mstStageClearTimeRewards->map(function (MstStageClearTimeReward $mstStageClearTimeReward) {
            return $mstStageClearTimeReward->reward;
        });

        $rewardInfos = $this->rewardInfoGetHandleService->build($rewardDtos)->getRewardInfos();

        $data = [];
        foreach ($mstStageClearTimeRewards as $mstStageClearTimeReward) {

            $rewardInfo = $rewardInfos->get($mstStageClearTimeReward->id);

            if ($rewardInfo === null) {
                continue;
            }

            $data[] = [
                'ID' => $mstStageClearTimeReward->id,
                '目標タイム' => $mstStageClearTimeReward->upper_clear_time_ms,
                '報酬情報' => $rewardInfo,
            ];
        }

        return $data;
    }

}
