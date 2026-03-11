# vd_tak_boss_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

「タコピーの原罪」の世界観を反映したボスブロックです。ボスとして「ハッピー星からの使者 タコピー」（Blue属性・防御ロール）が敵ゲート前に降臨します。タコピーは作品のタイトルキャラクターであり、プレイアブルキャラクターが敵として登場する c_ キャラです。プレイヤーはボスを倒すまで敵ゲートへのダメージが無効であるため、タコピーの撃破が最優先課題となります。tak作品固有の雑魚キャラは存在しないため、ボス登場後の雑魚波はすべてファントム（共通敵・Colorless属性・攻撃ロール）で構成します。ボス登場から0.5秒後にファントムが3体出現して圧力を加え、さらに3秒後にファントムが3体追加出現することで継続的な緊張感を維持します。タコピーは移動速度が低め（spd=25）・防御ロールの重量級ボスであり、1ダメージ受けると進軍を開始する仕様が戦術的な緊迫感を生み出します。フロア係数 1.00 を基準とした設計です。フェーズ切り替えはなく、SwitchSequenceGroup は使用しません。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| chara_tak_00001 | ハッピー星からの使者 タコピー | ボス | Blue属性・防御ロール・c_キャラ（プレイアブルが敵登場） |
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・攻撃ロール・tak固有雑魚なし |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv` (release_key: 202509010)
> 新規生成不要（既存IDをそのままMstAutoPlayerSequence.action_valueで参照）

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| c_tak_00001_vd_Boss_Blue | ハッピー星からの使者 タコピー | Boss | Defense | Blue | 10,000 | 300 | 25 | 0.17 | 4 | 5 | 400 |
| e_glo_00001_vd_Normal_Colorless | ファントム | Normal | Attack | Colorless | 5,000 | 100 | 34 | 0.22 | 3 | 1 | 150 |

---

### コマ設計

ボスブロックは1行1コマ固定。

```mermaid
block-beta
  columns 1
  A["row=1 / koma1\n幅=1.00\neffect: None"]:1
```

| row | height | コマ数 | koma1_width | 幅合計 |
|-----|--------|-------|-------------|--------|
| 1 | 1.0 | 1コマ | 1.0 | 1.0 |

---

### 敵キャラシーケンス設計

#### どのフェーズで、どの敵を、いつ、どこに、どのくらい出現させるか

```mermaid
flowchart LR
  Start((開始)) --> B1["InitialSummon\nハッピー星からの使者 タコピー ×1\nsummon_position=1.7\nBoss オーラ"]
  B1 --> W1["ElapsedTime 500ms\nファントム ×3\nDefault"]
  W1 --> W2["ElapsedTime 3000ms\nファントム ×3\nDefault"]
  W2 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数/召喚位置 |
|------|-------------|---|---|-----------------|
| 1 | InitialSummon | ハッピー星からの使者 タコピー (c_tak_00001_vd_Boss_Blue) | 1 | 1 / summon_position=1.7 |
| 2 | ElapsedTime 500ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 3 | 4 |
| 3 | ElapsedTime 3000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 3 | 7 |

> **c_キャラ召喚制約について**: ハッピー星からの使者 タコピー（chara_tak_00001）はプレイアブルキャラが敵として登場する c_ キャラです。同一トリガーで summon_count >= 2 かつ summon_interval = 0 の瞬間複数召喚は禁止されているため、InitialSummon で summon_count = 1 で設定します。

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波/フェーズ | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|-----------|---|---------|---------|------|----------|----------|-------|
| InitialSummon | ハッピー星からの使者 タコピー | 10,000 | 1.0 | 10,000 | 300 | 1.0 | 300 |
| ElapsedTime 500ms | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |
| ElapsedTime 3000ms | ファントム | 5,000 | 1.0 | 5,000 | 100 | 1.0 | 100 |

#### フェーズ切り替えはあるか

なし（VDではSwitchSequenceGroup使用禁止）

---

## 演出

### アセット

#### 背景

| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| loop_background_asset_key | （空） | VDの背景切り替えはゲームロジック側で管理 |
| フロア0以上 | koma_background_vd_00002 | クライアント側でフロア係数に応じて切り替え |
| フロア20以上 | koma_background_vd_00004 | 同上 |
| フロア40以上 | koma_background_vd_00006 | 同上 |

#### BGM

| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | SSE_SBG_003_004 | ボスブロック用BGM |

---

### 敵キャラオーラ

| オーラ種別 | 使用箇所 |
|----------|---------|
| Boss | ハッピー星からの使者 タコピー（InitialSummon時） |
| Default | ファントム（雑魚2波） |

---

### 敵キャラ召喚アニメーション

ボス（ハッピー星からの使者 タコピー）は `InitialSummon` で `summon_position=1.7`（ゲート付近）に配置。1ダメージ受けると進軍を開始する（`move_start_condition_type=Damage, move_start_condition_value=1`）。タコピーは移動速度 spd=25 と低め設定の重量型ボスであり、ゆっくり確実に前線へ向かってくるプレッシャーが特徴的な演出となる。
雑魚キャラ（ファントム）は `SummonEnemy` アクションによるElapsedTime時間差召喚。tak作品固有の雑魚キャラは存在しないため、全雑魚波ともファントムのみで構成されるシンプルな召喚演出となる。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 既存参照 | generated/ファントムマスター/ の既存データ使用（c_tak_00001_vd_Boss_Blue: 202509010、e_glo_00001_vd_Normal_Colorless: 202509010） |
| MstEnemyOutpost | 新規生成 | HP=1,000固定、is_damage_invalidation=空 |
| MstPage | 新規生成 | id=vd_tak_boss_00001 |
| MstKomaLine | 新規生成 | 1行固定（row=1, koma1_width=1.0） |
| MstAutoPlayerSequence | 新規生成 | 3要素（ボス1体+雑魚6体） |
| MstInGame | 新規生成 | ボスあり（boss_mst_enemy_stage_parameter_id=c_tak_00001_vd_Boss_Blue） |
