# インゲームマスタデータ設計書

## 基本情報

- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）ボスブロック
- ステージ種別: dungeon_boss

## インゲームID命名案

- インゲームID: `dungeon_mag_boss_00001`
- 命名根拠: dungeon_{シリーズ略称}_boss_{連番5桁} の規則に従い、シリーズID `mag`、第1弾ボスブロックを示す `00001` を付与

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（桜木カナ）、雑魚1種（冷却系怪異） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss固定） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeon boss固定） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス初期配置＋護衛2回） |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計

- `id`: `dungeon_mag_boss_00001`
- `content_type`: dungeon（限界チャレンジ）
- `bgm_asset_key`: `SSE_SBG_003_001`（マジルミエ通常戦闘BGM）
- `boss_bgm_asset_key`: （空欄）
- `loop_background_asset_key`: `mag_00004`（normalブロックと同一背景）
- `normal_enemy_hp_coef`: `1`
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`
- `boss_enemy_attack_coef`: `1`
- `boss_enemy_speed_coef`: `1`
- `boss_count`: `1`
- `boss_mst_enemy_stage_parameter_id`: `c_mag_00001_challange_Boss_Yellow`
- `description.ja`: 黄属性のボスが登場するので緑属性のキャラは有利に戦える。ボスを倒すまで敵ゲートへのダメージは無効。まずボスを集中撃破せよ。マジルミエの新人魔法少女・桜木カナが立ちはだかる決戦フロア

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 属性 | UnitKind |
|--------|------|----------------------|-----|-------|--------|------|---------|
| `c_mag_00001_challange_Boss_Yellow` | ボス（桜木カナ） | `chara_mag_00001` | 100,000 | 500 | 35 | Yellow | Boss |
| `e_mag_00001_general_Normal_Colorless` | 護衛雑魚（冷却系怪異） | `enemy_mag_00001` | 70,000 | 1,200 | 35 | Colorless | Normal |

補足：
- `e_mag_00001_general_Normal_Colorless` はnormalブロックで既に定義済みのパラメータIDと同一（汎用パラメータの再利用）
- ボスの `c_mag_00001_challange_Boss_Yellow` はdungeon専用の新規パラメータ
- ボスの `is_summon_unit_outpost_damage_invalidation=1` をMstAutoPlayerSequenceで設定し、撃破まで敵ゲートへのダメージを無効化

## MstAutoPlayerSequence ウェーブ構成設計

- 総シーケンス行数: 3行
- グループ切り替え: なし（単一デフォルトグループ）

| 行 | condition_type | condition_value | action_type | action_value | summon_count | 備考 |
|----|---------------|----------------|-------------|-------------|-------------|------|
| 1 | InitialSummon | 2 | SummonEnemy | c_mag_00001_challange_Boss_Yellow | 1 | ボスをゲート前に配置、ダメージ無効フラグON |
| 2 | ElapsedTime | 3000 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 | 3秒後に護衛雑魚1体 |
| 3 | ElapsedTime | 6000 | SummonEnemy | e_mag_00001_general_Normal_Colorless | 1 | 6秒後に護衛雑魚1体 |

## MstPage / MstKomaLine 構成

- ページID: `dungeon_mag_boss_00001`
- ページ数: 1
- コマ行数: **1行**（dungeon boss固定）
- コマレイアウト: 全幅1コマ（koma1_width=1.0）
- コマ効果: None（エフェクトなし）
- 背景アセット: `mag_00004`（normalブロックと同一）

## MstEnemyOutpost 設計

- `id`: `dungeon_mag_boss_00001`
- `hp`: `1000`（dungeon boss固定）
- `is_damage_invalidation`: 空欄（MstAutoPlayerSequenceのis_summon_unit_outpost_damage_invalidationでボス撃破まで無効化するため、砦自体のフラグは不要）

## 参照した既存データ

- normalブロック参照: `dungeon_mag_normal_00001`（mag normalの生成済みCSV）
  - BGM: `SSE_SBG_003_001`、背景: `mag_00004`、release_key: `999999999`
  - 敵パラメータ `e_mag_00001_general_Normal_Colorless`（HP=70,000 / 攻撃=1,200 / 速度=35）を再利用
- challenge参照: `event_spy1_challenge01_00001`（SPY challegeパターン）
  - 倍率は全て1.0が基本

## 不確定事項・要確認事項

- `e_mag_00001_general_Normal_Colorless` をbossブロックのCSVにも再定義するか、参照のみ（normalブロックから流用）か
  → 今回は別ファイルとして再定義する方針（bossブロック単体で投入可能な形式）
- `outpost_asset_key` / `artwork_asset_key`: 既存dungeon データがないため空欄で生成し、後で設定する
- `release_key`: 999999999（開発テスト用）を設定
