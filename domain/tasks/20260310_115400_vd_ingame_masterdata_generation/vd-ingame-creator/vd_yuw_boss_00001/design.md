# vd_yuw_boss_00001 インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: 202604010

## インゲーム要件テキスト

2.5次元の誘惑の世界観を反映したボスブロックです。ボスとして「奥村 正宗」（Blue属性・防御ロール）が敵ゲート前に降臨します。コスプレ部の顧問を務める奥村 正宗は、生徒たちに厳しくも愛情深い存在であり、その威圧感と粘り強さをボスの特性として表現しています。防御ロールらしく移動速度が低め（spd=25）であることから、プレイヤーはじっくり削り続けることを余儀なくされます。プレイヤーはボスを倒すまで敵ゲートへのダメージが無効であるため、ボスの撃破が最優先課題となります。ボス登場から0.5秒後に天乃リリサ（chara_yuw_00001）が1体出現してプレッシャーを与え、さらに3秒後にファントムが3体追加出現することで継続的な圧力を維持します。yuwキャラクターはすべてBlue属性の c_ キャラ（プレイアブルキャラが敵として登場）であるため、c_キャラ瞬間複数召喚禁止のルールに従い、雑魚はsummon_count=1で順次出現させます。フロア係数 1.00 を基準とした設計です。

---

## レベルデザイン

### 敵キャラ設計

#### 敵キャラ選定（MstEnemyCharacter）

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| chara_yuw_00601 | 奥村 正宗 | ボス | Blue属性・防御ロール |
| chara_yuw_00001 | リリエルに捧ぐ愛 天乃 リリサ | 雑魚 | Blue属性・攻撃ロール |
| enemy_glo_00001 | ファントム | 雑魚（共通） | Colorless属性・攻撃ロール |

#### 敵キャラステータス（MstEnemyStageParameter）

> 既存参照: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/generated/ファントムマスター/MstEnemyStageParameter.csv` (release_key: 202511020 / 202509010)
> 新規生成不要（既存IDをそのままMstAutoPlayerSequence.action_valueで参照）

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | knockback | combo | drop_bp |
|--------------------------|---------|------|------|-------|---------|----------|----------|-----------|-----------|-------|---------|
| c_yuw_00601_vd_Boss_Blue | 奥村 正宗 | Boss | Defense | Blue | 10,000 | 300 | 25 | 0.19 | 3 | 4 | 1,000 |
| c_yuw_00001_vd_Normal_Blue | リリエルに捧ぐ愛 天乃 リリサ | Normal | Attack | Blue | 10,000 | 300 | 30 | 0.24 | 3 | 4 | 1,000 |
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
  Start((開始)) --> B1["InitialSummon\n奥村 正宗 ×1\nsummon_position=1.7\nBoss オーラ"]
  B1 --> W1["ElapsedTime 500ms\n天乃 リリサ ×1\nDefault"]
  W1 --> W2["ElapsedTime 3000ms\nファントム ×3\nDefault"]
  W2 --> End((終了))
```

| elem | 出現タイミング | 敵 | 数 | 累計出現数/召喚位置 |
|------|-------------|---|---|-----------------|
| 1 | InitialSummon | 奥村 正宗 (c_yuw_00601_vd_Boss_Blue) | 1 | 1 / summon_position=1.7 |
| 2 | ElapsedTime 500ms | リリエルに捧ぐ愛 天乃 リリサ (c_yuw_00001_vd_Normal_Blue) | 1 | 2 |
| 3 | ElapsedTime 3000ms | ファントム (e_glo_00001_vd_Normal_Colorless) | 3 | 5 |

> **c_キャラ召喚制約について**: 天乃リリサ（c_yuw_00001）はプレイアブルキャラが敵として登場するc_キャラです。同一トリガーでsummon_count >= 2かつsummon_interval = 0の瞬間複数召喚は禁止されているため、summon_count = 1で設定します。

#### 敵キャラの固有ステータス調整（hp_coef / atk_coef）

| 波/フェーズ | 敵 | base_hp | hp_coef | 実HP | base_atk | atk_coef | 実ATK |
|-----------|---|---------|---------|------|----------|----------|-------|
| InitialSummon | 奥村 正宗 | 10,000 | 1.0 | 10,000 | 300 | 1.0 | 300 |
| ElapsedTime 500ms | 天乃 リリサ | 10,000 | 1.0 | 10,000 | 300 | 1.0 | 300 |
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
| Boss | 奥村 正宗（InitialSummon時） |
| Default | 天乃 リリサ、ファントム（雑魚2種） |

---

### 敵キャラ召喚アニメーション

ボス（奥村 正宗）は `InitialSummon` で `summon_position=1.7`（ゲート付近）に配置。1ダメージ受けると進軍を開始する（`move_start_condition_type=Damage, move_start_condition_value=1`）。防御ロールらしく移動速度が低め（spd=25）であるため、プレイヤーは長期戦を覚悟してボスを攻略する必要がある。
雑魚キャラ（天乃 リリサ・ファントム）は `SummonEnemy` アクションによるElapsedTime時間差召喚。天乃 リリサはc_キャラのため summon_count=1 で1体ずつ召喚する。

---

## 生成テーブルまとめ

| テーブル | 状態 | 備考 |
|---------|------|------|
| MstEnemyStageParameter | 既存参照 | generated/ファントムマスター/ の既存データ使用（release_key: 202511020 / 202509010） |
| MstEnemyOutpost | 新規生成 | HP=1,000固定、is_damage_invalidation=空 |
| MstPage | 新規生成 | id=vd_yuw_boss_00001 |
| MstKomaLine | 新規生成 | 1行固定（row=1, koma1_width=1.0） |
| MstAutoPlayerSequence | 新規生成 | 3要素（ボス1体+雑魚4体） |
| MstInGame | 新規生成 | ボスあり（boss_mst_enemy_stage_parameter_id=c_yuw_00601_vd_Boss_Blue） |
