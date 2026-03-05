# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）
- ステージ種別: dungeon_boss（ボスブロック）

## インゲームID命名案
- インゲームID: `dungeon_dan_boss_00001`
- 命名根拠: dungeon_{シリーズ}_boss_{連番5桁} の規則に従い、シリーズID `dan`、ボスブロック第1弾として命名

## 生成対象テーブル一覧
| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（オカルン）、雑魚1体（セルポ星人） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon_boss固定） |
| MstPage | 1件 | |
| MstKomaLine | 1件 | 1行構成（dungeon_boss固定） |
| MstAutoPlayerSequence | 3件 | ボス初期配置1行 + 雑魚時間差2行 |
| MstInGame | 1件 | |

## MstInGame 主要パラメータ設計
- `id`: `dungeon_dan_boss_00001`
- `bgm_asset_key`: `SSE_SBG_003_001`（ダンダダン通常BGM）
- `boss_bgm_asset_key`: 空（ボスBGMなし、通常BGMが全編通じて流れる）
- `loop_background_asset_key`: `dan_00005`
- `boss_mst_enemy_stage_parameter_id`: `c_dan_00002_dan_dungeon_Boss_Blue`
- `boss_count`: `1`
- `normal_enemy_hp_coef`: `1`（雑魚HP倍率 等倍）
- `normal_enemy_attack_coef`: `1`
- `normal_enemy_speed_coef`: `1`
- `boss_enemy_hp_coef`: `1`（ボスHP倍率。MstEnemyStageParameterで直接低めに設定するため1倍）
- `boss_enemy_attack_coef`: `1`（ボス攻撃力倍率。MstEnemyStageParameterで直接高めに設定するため1倍）
- `boss_enemy_speed_coef`: `1`

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | character_unit_kind | color |
|--------|------|----------------------|-----|-------|--------|---------------------|-------|
| `c_dan_00002_dan_dungeon_Boss_Blue` | ボス（ターボババアの霊力 オカルン） | `chara_dan_00002` | 30,000 | 800 | 10 | Boss | Blue |
| `e_dan_00101_dan_dungeon_Normal_Colorless` | 護衛雑魚（セルポ星人（変身）） | `enemy_dan_00101` | 1,000 | 50 | 47 | Normal | Colorless |

- ボスHP設計根拠: dungeon仕様「通常登場より低いHP」に合わせ、chara系のベース値10,000に対してHP係数を抑える形で30,000に設定。攻撃力は高め（800）に設定しプレイヤーへの脅威を演出。
- ボス移動速度: 敵ゲート前に待機し、ダメージを受けると前進する設計のため、move_speed=10の遅め設定
- 雑魚HP: 1,000（要件テキスト通りのベース値）、攻撃力: 50（ベース値）、移動速度: 47（高速援軍として機能）

## MstAutoPlayerSequence ウェーブ構成設計
- 総シーケンス行数: 3行
- グループ切り替え: なし（デフォルトグループのみ）
- 行1: `InitialSummon(0)` → ボス（オカルン）×1体 砦前配置（summon_position=1.7）、Damageトリガーで移動開始、aura_type=Boss、is_summon_unit_outpost_damage_invalidation=1
- 行2: `ElapsedTime(2000ms)` → 護衛雑魚（セルポ星人）×1体
- 行3: `ElapsedTime(4000ms)` → 護衛雑魚（セルポ星人）×1体（追加援軍）

## MstPage / MstKomaLine 構成
- ページ数: 1
- コマ行数: **1行**（dungeon_boss固定）
- コマ効果: None（エフェクトなし）
- コマ幅: 全幅1.0（koma1のみ使用）
- layout_asset_key: `1`（1コマ・幅1.0のシンプル構成）
- koma1_asset_key: `dan_00005`（ループ背景アセットと同一）

## MstEnemyOutpost 設計
- `id`: `dungeon_dan_boss_00001`
- `hp`: `1,000`（dungeon_boss固定）
- `is_damage_invalidation`: 空（ゲートへのダメージ自体は有効だが、ボス生存中はシーケンス設定でダメージ無効化）

## ステージ説明文（MstInGame.description.ja）
「ボスを倒すまでゲートにダメージが通らないぞ！青属性の敵には黄属性キャラが有利。強力なボスを優先して撃破せよ！」

## 参照した既存データ
- 参照ステージ: `dungeon_spy_normal_00001`（SPY normalブロック）
- 参照クエリ結果: release_key=999999999、コマライン1行=height:0.55/layout:1/width:1.0のパターン確認
- 最新リリースキー: `202604010`（本データは開発テスト用として `999999999` を使用）

## 不確定事項・要確認事項
- `outpost_asset_key` および `player_outpost_asset_key`: SPYデータと同様に空欄で設定
- リリースキー: 投入タイミング確定後に `999999999` から実際のリリースキーへ変更が必要
