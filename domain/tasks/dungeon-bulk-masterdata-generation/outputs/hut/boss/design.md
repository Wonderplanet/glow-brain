# インゲームマスタデータ設計書

## 基本情報
- 生成日時: 2026-03-01
- コンテンツ種別: dungeon（限界チャレンジ）ボスブロック（Boss Block）
- ステージ種別: dungeon_boss
- シリーズ: hut（ふつうの軽音部）

## インゲームID
- インゲームID: `dungeon_hut_boss_00001`
- 命名根拠: `dungeon_{シリーズ}_boss_{連番5桁}` の命名規則に従う

## 生成対象テーブル一覧

| テーブル | 生成件数 | 備考 |
|---------|---------|------|
| MstEnemyStageParameter | 2件 | ボス1体（chara_hut_00001）、雑魚1種（enemy_glo_00001） |
| MstEnemyOutpost | 1件 | HP: 1,000（dungeon boss固定値） |
| MstPage | 1件 | id: dungeon_hut_boss_00001 |
| MstKomaLine | 1件 | 1行固定（dungeon bossブロック仕様） |
| MstAutoPlayerSequence | 3件 | 3行構成（ボス即時配置 + 護衛雑魚2波） |
| MstInGame | 1件 | id: dungeon_hut_boss_00001 |

## MstInGame 主要パラメータ設計

| パラメータ | 値 | 備考 |
|----------|-----|------|
| `id` | `dungeon_hut_boss_00001` | |
| `bgm_asset_key` | `SSE_SBG_003_002` | 通常バトル曲（spy normalと同じ） |
| `boss_bgm_asset_key` | （空） | 設定なし |
| `loop_background_asset_key` | （空） | hut専用背景アセット未存在 |
| `player_outpost_asset_key` | （空） | |
| `mst_page_id` | `dungeon_hut_boss_00001` | |
| `mst_enemy_outpost_id` | `dungeon_hut_boss_00001` | |
| `boss_mst_enemy_stage_parameter_id` | `c_hut_00001_dungeon_boss_Boss_Colorless` | ボスパラメータID |
| `boss_count` | `1` | |
| `normal_enemy_hp_coef` | `1` | 等倍 |
| `normal_enemy_attack_coef` | `1` | 等倍 |
| `normal_enemy_speed_coef` | `1` | 等倍 |
| `boss_enemy_hp_coef` | `1` | 等倍 |
| `boss_enemy_attack_coef` | `1` | 等倍 |
| `boss_enemy_speed_coef` | `1` | 等倍 |
| `release_key` | `999999999` | 開発・テスト用 |

## MstEnemyStageParameter 敵パラメータ設計

| 識別子 | 役割 | mst_enemy_character_id | HP | 攻撃力 | スピード | 索敵距離 | コンボサイクル | color | kind |
|--------|------|----------------------|-----|-------|--------|---------|-------------|-------|------|
| `c_hut_00001_dungeon_boss_Boss_Colorless` | ボス | `chara_hut_00001` | 5,000 | 300 | 35 | 0.21 | 5 | Colorless | Boss |
| `e_glo_00001_hut1_dungeon_Normal_Colorless` | 護衛雑魚 | `enemy_glo_00001` | 1,000 | 100 | 40 | 0.2 | 1 | Colorless | Normal |

**ボス設計根拠:**
- dungeon仕様「通常登場よりHP低め・攻撃力高め」に従いHP 5,000・攻撃力 300 を設定
- `is_summon_unit_outpost_damage_invalidation = 1`：ボスが場にいる間、敵ゲートへのダメージ無効
- Defense（防衛型）ロールを採用（URキャラの固定特性）

## MstEnemyOutpost 設計

| パラメータ | 値 | 備考 |
|----------|-----|------|
| `id` | `dungeon_hut_boss_00001` | |
| `hp` | `1,000` | dungeon bossブロック固定値 |
| `is_damage_invalidation` | （空） | シーケンス側でダメージ無効を制御 |

## MstAutoPlayerSequence シーケンス設計

| 行番号 | id | condition_type | condition_value | action_type | action_value | summon_count | is_summon_unit_outpost_damage_invalidation | aura_type | 備考 |
|--------|-----|--------------|----------------|-------------|-------------|--------------|------------------------------------------|-----------|------|
| 1 | `dungeon_hut_boss_00001_1` | ElapsedTime | 0 | SummonEnemy | `c_hut_00001_dungeon_boss_Boss_Colorless` | 1 | 1 | Boss | ボス即時配置・ゲートダメージ無効 |
| 2 | `dungeon_hut_boss_00001_2` | ElapsedTime | 20 | SummonEnemy | `e_glo_00001_hut1_dungeon_Normal_Colorless` | 1 | 0 | Default | 護衛雑魚・第1波（2,000ms） |
| 3 | `dungeon_hut_boss_00001_3` | ElapsedTime | 40 | SummonEnemy | `e_glo_00001_hut1_dungeon_Normal_Colorless` | 1 | 0 | Default | 護衛雑魚・第2波（4,000ms） |

**注:** condition_value は100ms単位。2,000ms → 20、4,000ms → 40

**ボスの配置:** summon_position = 1.7（砦付近）、move_start_condition_type = Damage、move_start_condition_value = 1（1ダメージを受けたら移動開始）

## MstPage / MstKomaLine 構成

- ページID: `dungeon_hut_boss_00001`
- コマ行数: **1行**（dungeon bossブロック固定）
- コマ効果: `None`（特殊効果なし）
- コマアセット: `glo_00014`
- コマ幅: 1.0（1コマ全幅）
- koma_line_layout_asset_key: `1`（1コマ全幅）

## テキスト設計

| 項目 | 内容 |
|------|------|
| バトルヒント (result_tips.ja) | ひたむきギタリスト 鳩野 ちひろが行く手を阻む！ボスを倒すまで敵ゲートにダメージが入らないぞ！全キャラで挑め！ |
| ステージ説明文 (description.ja) | 無属性の強敵が待ち構えている。ボスを撃破するまでゲートは守られる。全力で立ち向かおう！ |

## 参照した既存データ

- 参照データ: `dungeon_spy_normal_00001`（SPY×FAMILYのnormalブロック。bossブロックはCSV未存在のため構造を参考に設計）
- BGMキー `SSE_SBG_003_002`、コマアセットパターン、MstAutoPlayerSequenceの列構造を参照

## 不確定事項・要確認事項

- `glo_00014` コマアセットキーの存在確認が必要
- `chara_hut_00001` / `enemy_glo_00001` が MstEnemyCharacter に登録済みか確認推奨
- release_key は投入時に正式なリリースキーへ変更すること
