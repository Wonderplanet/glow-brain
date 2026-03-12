-- VD Ingame Masterdata SQLite Schema
-- ENABLEカラムはエクスポート時に付加するため、テーブルには含めない
-- NULL値はエクスポート時に空文字列として出力する
-- CHECK制約はINSERT時にenum値を検証する（NULL値は許容）
-- '__NULL__' デフォルト値はexport_csv.pyによりそのままCSVに出力される（nullable列用）

PRAGMA journal_mode=WAL;
PRAGMA foreign_keys=ON;

-- =============================================================
-- 1. MstEnemyStageParameter
--    既存CSVからインポートして使用（新規INSERT禁止）
--    CSVのキャメルケース3カラムはスネークケースで保管しSELECT時にASエイリアスで変換
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_enemy_stage_parameters (
    id TEXT PRIMARY KEY,
    release_key INTEGER DEFAULT 1,
    mst_enemy_character_id TEXT,
    character_unit_kind TEXT DEFAULT 'Normal' CHECK (character_unit_kind IN ('Normal','Boss','AdventBattleBoss')),
    role_type TEXT CHECK (role_type IN ('None','Attack','Balance','Defense','Support','Unique','Technical','Special')),
    color TEXT CHECK (color IN ('None','Colorless','Red','Blue','Yellow','Green')),
    sort_order INTEGER,
    hp INTEGER,
    damage_knock_back_count INTEGER,
    move_speed INTEGER,
    well_distance REAL,
    attack_power INTEGER,
    attack_combo_cycle INTEGER,
    mst_unit_ability_id1 TEXT DEFAULT '',
    drop_battle_point INTEGER,
    mst_transformation_enemy_stage_parameter_id TEXT DEFAULT '',
    transformation_condition_type TEXT DEFAULT 'None' CHECK (transformation_condition_type IN ('None','HpPercentage','StageTime')),
    transformation_condition_value TEXT DEFAULT ''
);

-- =============================================================
-- 2. MstEnemyOutpost
--    VD: boss=1000固定, normal=100固定
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_enemy_outposts (
    id TEXT PRIMARY KEY,
    hp INTEGER NOT NULL,
    is_damage_invalidation INTEGER NOT NULL DEFAULT 0,
    outpost_asset_key TEXT NOT NULL DEFAULT '',
    artwork_asset_key TEXT NOT NULL DEFAULT '',
    release_key INTEGER DEFAULT 1
);

-- =============================================================
-- 3. MstPage
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_pages (
    id TEXT PRIMARY KEY,
    release_key INTEGER DEFAULT 1
);

-- =============================================================
-- 4. MstKomaLine
--    koma2~4は存在しない場合にNULL可（エクスポート時は空文字列）
--    koma[1-4]_effect_type: KomaEffectType enum
--    koma[1-4]_effect_target_side: KomaEffectTargetSide enum
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_koma_lines (
    id TEXT PRIMARY KEY,
    mst_page_id TEXT,
    row INTEGER,
    height REAL,
    koma_line_layout_asset_key TEXT,
    -- koma1（必須）
    koma1_asset_key TEXT,
    koma1_width REAL,
    koma1_back_ground_offset REAL,
    koma1_effect_type TEXT DEFAULT 'None' CHECK (koma1_effect_type IN ('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Gust','Poison','Darkness','Burn','Stun','Freeze','Weakening')),
    koma1_effect_parameter1 TEXT DEFAULT '0',
    koma1_effect_parameter2 TEXT DEFAULT '0',
    koma1_effect_target_side TEXT DEFAULT 'All' CHECK (koma1_effect_target_side IN ('All','Player','Enemy')),
    koma1_effect_target_colors TEXT DEFAULT 'All',
    koma1_effect_target_roles TEXT DEFAULT 'All',
    -- koma2（オプション）
    koma2_asset_key TEXT,
    koma2_width REAL,
    koma2_back_ground_offset REAL,
    koma2_effect_type TEXT DEFAULT 'None' CHECK (koma2_effect_type IN ('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Gust','Poison','Darkness','Burn','Stun','Freeze','Weakening')),
    koma2_effect_parameter1 TEXT NOT NULL DEFAULT '',
    koma2_effect_parameter2 TEXT NOT NULL DEFAULT '',
    koma2_effect_target_side TEXT DEFAULT 'All' CHECK (koma2_effect_target_side IN ('All','Player','Enemy')),
    koma2_effect_target_colors TEXT DEFAULT 'All',
    koma2_effect_target_roles TEXT DEFAULT 'All',
    -- koma3（オプション）
    koma3_asset_key TEXT,
    koma3_width REAL,
    koma3_back_ground_offset REAL,
    koma3_effect_type TEXT DEFAULT 'None' CHECK (koma3_effect_type IN ('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Gust','Poison','Darkness','Burn','Stun','Freeze','Weakening')),
    koma3_effect_parameter1 TEXT NOT NULL DEFAULT '',
    koma3_effect_parameter2 TEXT NOT NULL DEFAULT '',
    koma3_effect_target_side TEXT CHECK (koma3_effect_target_side IN ('All','Player','Enemy')),
    koma3_effect_target_colors TEXT,
    koma3_effect_target_roles TEXT,
    -- koma4（オプション）
    koma4_asset_key TEXT,
    koma4_width REAL,
    koma4_back_ground_offset REAL,
    koma4_effect_type TEXT DEFAULT 'None' CHECK (koma4_effect_type IN ('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Gust','Poison','Darkness','Burn','Stun','Freeze','Weakening')),
    koma4_effect_parameter1 TEXT NOT NULL DEFAULT '',
    koma4_effect_parameter2 TEXT NOT NULL DEFAULT '',
    koma4_effect_target_side TEXT CHECK (koma4_effect_target_side IN ('All','Player','Enemy')),
    koma4_effect_target_colors TEXT,
    koma4_effect_target_roles TEXT,
    release_key INTEGER DEFAULT 1
);

-- =============================================================
-- 5. MstAutoPlayerSequence
--    condition_type: AutoPlayerSequenceConditionType
--    action_type: AutoPlayerSequenceActionType
--    summon_animation_type: SummonAnimationType
--    aura_type: UnitAuraType
--    death_type: UnitDeathType
--    move_start/restart_condition_type: MoveStartConditionType
--    move_stop_condition_type: MoveStopConditionType
--    deactivation_condition_type: AutoPlayerSequenceConditionType
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_auto_player_sequences (
    id TEXT PRIMARY KEY,
    sequence_set_id TEXT NOT NULL,
    sequence_group_id TEXT,
    sequence_element_id TEXT NOT NULL,
    priority_sequence_element_id TEXT DEFAULT '__NULL__',
    condition_type TEXT NOT NULL DEFAULT 'None' CHECK (condition_type IN (
        'None','ElapsedTime','OutpostDamage','OutpostHpPercentage',
        'InitialSummon','EnterTargetKomaIndex','DarknessKomaCleared',
        'FriendUnitDead','FriendUnitTransform','FriendUnitSummoned',
        'SequenceElementActivated','ElapsedTimeSinceSequenceGroupActivated'
    )),
    condition_value TEXT,
    action_type TEXT NOT NULL DEFAULT 'None' CHECK (action_type IN (
        'None','SummonEnemy','SummonPlayerCharacter','SwitchSequenceGroup',
        'PlayerSpecialAttack','SummonPlayerSpecialCharacter',
        'SummonGimmickObject','TransformGimmickObjectToEnemy'
    )),
    action_value TEXT,
    action_value2 TEXT DEFAULT '',
    summon_count INTEGER DEFAULT 0,
    summon_interval INTEGER DEFAULT 0,
    summon_animation_type TEXT DEFAULT 'None' CHECK (summon_animation_type IN ('None','Fall0','Fall','Fall4')),
    summon_position REAL NOT NULL DEFAULT 0,
    move_start_condition_type TEXT DEFAULT 'None' CHECK (move_start_condition_type IN (
        'None','ElapsedTime','FoeEnterSameKoma','EnterTargetKoma','Damage','DeadFriendUnitCount'
    )),
    move_start_condition_value INTEGER NOT NULL DEFAULT 0,
    move_stop_condition_type TEXT DEFAULT 'None' CHECK (move_stop_condition_type IN (
        'None','ElapsedTime','TargetPosition','PassedKomaCount'
    )),
    move_stop_condition_value INTEGER NOT NULL DEFAULT 0,
    move_restart_condition_type TEXT DEFAULT 'None' CHECK (move_restart_condition_type IN (
        'None','ElapsedTime','FoeEnterSameKoma','EnterTargetKoma','Damage','DeadFriendUnitCount'
    )),
    move_restart_condition_value INTEGER NOT NULL DEFAULT 0,
    move_loop_count INTEGER NOT NULL DEFAULT 0,
    is_summon_unit_outpost_damage_invalidation INTEGER NOT NULL DEFAULT 0,
    last_boss_trigger INTEGER NOT NULL DEFAULT 0,
    aura_type TEXT DEFAULT 'Default' CHECK (aura_type IN ('Default','Boss','AdventBoss1','AdventBoss2','AdventBoss3')),
    death_type TEXT DEFAULT 'Normal' CHECK (death_type IN ('Normal','Escape')),
    enemy_hp_coef REAL DEFAULT 1,
    enemy_attack_coef REAL DEFAULT 1,
    enemy_speed_coef REAL DEFAULT 1,
    override_drop_battle_point INTEGER DEFAULT '__NULL__',
    defeated_score INTEGER DEFAULT 0,
    action_delay INTEGER NOT NULL DEFAULT 0,
    deactivation_condition_type TEXT DEFAULT 'None' CHECK (deactivation_condition_type IN (
        'None','ElapsedTime','OutpostDamage','OutpostHpPercentage',
        'InitialSummon','EnterTargetKomaIndex','DarknessKomaCleared',
        'FriendUnitDead','FriendUnitTransform','FriendUnitSummoned',
        'SequenceElementActivated','ElapsedTimeSinceSequenceGroupActivated'
    )),
    deactivation_condition_value TEXT NOT NULL DEFAULT '',
    release_key INTEGER DEFAULT 1
);

-- =============================================================
-- 6. MstInGame
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_in_games (
    id TEXT PRIMARY KEY,
    mst_auto_player_sequence_id TEXT DEFAULT '',
    mst_auto_player_sequence_set_id TEXT,
    bgm_asset_key TEXT DEFAULT '',
    boss_bgm_asset_key TEXT DEFAULT '',
    loop_background_asset_key TEXT DEFAULT '',
    player_outpost_asset_key TEXT DEFAULT '',
    mst_page_id TEXT DEFAULT '',
    mst_enemy_outpost_id TEXT DEFAULT '',
    mst_defense_target_id TEXT DEFAULT '__NULL__',
    boss_mst_enemy_stage_parameter_id TEXT DEFAULT '',
    boss_count INTEGER DEFAULT '__NULL__',
    normal_enemy_hp_coef REAL DEFAULT 1,
    normal_enemy_attack_coef REAL DEFAULT 1,
    normal_enemy_speed_coef REAL DEFAULT 1,
    boss_enemy_hp_coef REAL DEFAULT 1,
    boss_enemy_attack_coef REAL DEFAULT 1,
    boss_enemy_speed_coef REAL DEFAULT 1,
    release_key INTEGER DEFAULT 1
);

-- =============================================================
-- 7. MstInGameI18n（result_tips/descriptionがある場合のみINSERT）
-- =============================================================
CREATE TABLE IF NOT EXISTS mst_in_games_i18n (
    id TEXT PRIMARY KEY,
    mst_in_game_id TEXT,
    language TEXT,
    result_tips TEXT,
    description TEXT
);
