# 限界チャレンジ（vd）インゲーム設計書 入力テンプレート

> **使い方**
> このテンプレートを複製し、各項目を埋めてください。
> `[TODO]` タグは未決定の項目です。決定したら置き換えてください。
> 記入後、このファイルを元にインゲーム設計書を生成できます。
>
> **参照**: `domain/knowledge/masterdata/in-game/限界チャレンジ（vd）マスタデータ要件.md`

---

## 基本情報

| 項目 | 値 |
|------|-----|
| シリーズキー | `[TODO: 例 kai / dan / spy]` |
| コンテンツ名称 | `[TODO: 例 ○○シリーズ 限界チャレンジ]` |
| リリースキー | `[TODO: 例 202601010]` |

---

## コンセプト・設計意図

この限界チャレンジのコンセプトや設計意図を自由記述してください。
ここの内容が「インゲーム要件テキスト」セクションの元になります。

```
[TODO: 以下の観点を参考に記入]

- 登場する敵キャラクターのテーマ・属性カラー
- bossブロックのボスキャラの特徴と演出意図
- normalブロックの難度曲線（序盤→後半でどう変化するか）
- シリーズ全体としての体験イメージ
```

---

## ブロック構成一覧

> **固定ルール**: 1作品 = bossブロック 1個 + normalブロック N個
> IDはすべて `vd_{シリーズ}_{種別}_{連番5桁}` 形式

| ブロック種別 | インゲームID | コマ行数 | 砦HP | 備考 |
|------------|-----------|:-------:|-----:|------|
| vd_boss | `vd_[TODO]_boss_00001` | **1行（固定）** | **1,000（固定）** | |
| vd_normal | `vd_[TODO]_normal_00001` | **3行（固定）** | **100（固定）** | |
| vd_normal | `vd_[TODO]_normal_00002` | **3行（固定）** | **100（固定）** | |
| vd_normal | `vd_[TODO]_normal_00003` | **3行（固定）** | **100（固定）** | |
| （必要な分だけ追加） | | | | |

> `MstInGame.id = MstAutoPlayerSequence.sequence_set_id = MstPage.id = MstEnemyOutpost.id` はすべて同一値

---

## 共通設定：使用する敵キャラクター

このシリーズで登場するキャラクターモデルを列挙してください。

| mst_enemy_character_id | 日本語名 | 役割 | 備考 |
|------------------------|---------|------|------|
| `[TODO: 例 enemy_kai_00001]` | `[TODO]` | `[TODO: ボス / 雑魚A / 雑魚B]` | `[TODO: 速度感・特徴など]` |
| | | | |
| | | | |

---

## 共通設定：敵ステータス素値（MstEnemyStageParameter）

boss / normal 共通で使用するパラメータ一覧です。
全ブロックで共通のパラメータセットを使い、係数（hp_coef / atk_coef）でブロックごとの強さを調整します。

**ID命名規則**: `{character_id}_{シリーズ}_vd_{kind}_{color}`
（例: `c_kai_00201_kai_vd_Boss_Red` / `e_kai_00001_kai_vd_Normal_Colorless`）

| MstEnemyStageParameter ID | 日本語名 | kind | role | color | base_hp | base_atk | base_spd | well_dist | drop_bp |
|--------------------------|---------|------|------|-------|---------|---------|---------|-----------|---------|
| `[TODO: ボスID]` | | Boss | `[TODO]` | `[TODO]` | `[TODO]` | `[TODO]` | `[TODO]` | `[TODO]` | `[TODO]` |
| `[TODO: 雑魚ID]` | | Normal | | Colorless | | | | | |
| `[TODO]` | | Normal | | `[TODO]` | | | | | |

> **kind**: Normal / Boss / AdventBattleBoss
> **color**: Colorless / Red / Blue / Green / Yellow / Purple
> **role**: Attack / Defense / Technical
> **well_dist**: 砦到達時の基準距離（0.15〜0.35程度）
> **drop_bp**: 撃破時のBP付与量

---

## bossブロック設計

> **固定値**: コマ行数=1行（row=1, height=1.0, koma1_width=1.0） / 砦HP=1,000 / is_damage_invalidation=空

### インゲーム設定（MstInGame）

| 項目 | 値 |
|------|-----|
| id | `vd_[TODO]_boss_00001` |
| content_type | `Vd`（固定） |
| stage_type | `vd_boss`（固定） |
| boss_mst_enemy_stage_parameter_id | `[TODO: ボスのMstEnemyStageParameter ID]` |
| BGM（bgm_asset_key） | `[TODO: 例 SSE_SBG_003_001]` |
| ボスBGM（boss_bgm_asset_key） | `[TODO: なし / 例 SSE_SBG_003_004]` |
| コマアセット（koma_asset_key） | `[TODO: 例 kai_00001]` |
| 砦アセット（outpost_asset_key） | `[TODO: 例 kai_0001]` |

### シーケンス設計（MstAutoPlayerSequence）

> **ルール**: フェーズ切り替え（SwitchSequenceGroup）不使用 / action_type = SummonEnemy のみ / デフォルトグループのみ

| 行 | 出現条件 | 敵（MstEnemyStageParameter ID） | 数 | 召喚位置 | aura_type | 備考 |
|----|---------|-------------------------------|---|---------|-----------|------|
| 1 | `InitialSummon` | `[TODO: ボスID]` | 1 | `1.7`（砦付近） | `Boss` | move_start_condition=Damage(1) |
| 2 | `ElapsedTime(500)` | `[TODO: 雑魚ID]` | `[TODO]` | ランダム | `Default` | |
| 3 | `ElapsedTime(3000)` | `[TODO: 雑魚ID]` | `[TODO]` | ランダム | `Default` | 任意 |

### ステータス係数（hp_coef / atk_coef）

| 敵（日本語名 / color / kind） | base_hp | hp_coef | 実HP（目安） | base_atk | atk_coef | 実ATK（目安） | defeated_score |
|--------------------------|---------|---------|------------|---------|---------|-------------|----------------|
| `[TODO: ボス]` | | | | | | | |
| `[TODO: 雑魚]` | | | | | | | |

---

## normalブロック設計

### normalブロック共通設定

全normalブロックで共通の設定を記入してください。

| 項目 | 値 |
|------|-----|
| content_type | `Vd`（固定） |
| stage_type | `vd_normal`（固定） |
| boss_mst_enemy_stage_parameter_id | 空（固定） |
| BGM（bgm_asset_key） | `[TODO: 例 SSE_SBG_003_001]` |
| ボスBGM（boss_bgm_asset_key） | `[TODO: なし / 例 SSE_SBG_003_004]` |
| コマアセット（koma_asset_key） | `[TODO: 例 kai_00001]` |
| 砦アセット（outpost_asset_key） | `[TODO: 例 kai_0001]` |

### normalブロック コマ設計（3行固定）

> コマ効果（koma_effect_type）は指定がない場合 `None`。`koma1_effect_target_side` はエフェクトなしでも `All` を設定。

#### 行パターン一覧（`specs/コマ設計_行パターン.csv` より）

| 行パターンID | コマ数 | パターン名 | コマ幅1 | コマ幅2 | コマ幅3 | コマ幅4 |
|------------|------|-----------|--------|--------|--------|--------|
| 1 | 1コマ | 1コマのみ | 1.00 | — | — | — |
| 2 | 2コマ | 右ちょい長 | 0.60 | 0.40 | — | — |
| 3 | 2コマ | 左ちょい長 | 0.40 | 0.60 | — | — |
| 4 | 2コマ | がっつり右長 | 0.75 | 0.25 | — | — |
| 5 | 2コマ | がっつり左長 | 0.25 | 0.75 | — | — |
| 6 | 2コマ | 2等分 | 0.50 | 0.50 | — | — |
| 7 | 3コマ | 3等分 | 0.33 | 0.34 | 0.33 | — |
| 8 | 3コマ | 右広い | 0.50 | 0.25 | 0.25 | — |
| 9 | 3コマ | 中央広い | 0.25 | 0.50 | 0.25 | — |
| 10 | 3コマ | 左広い | 0.25 | 0.25 | 0.50 | — |
| 11 | 3コマ | 中央狭い | 0.40 | 0.20 | 0.40 | — |
| 12 | 4コマ | 4等分 | 0.25 | 0.25 | 0.25 | 0.25 |

#### 行パターン選択（全normalブロック共通）

| row | 行パターンID | height | コマ効果（effect） | offset |
|-----|------------|--------|-----------------|--------|
| 1 | `[TODO: 1〜12]` | `[TODO: 例 0.55]` | `[TODO: None]` | `[TODO: 例 -1.0]` |
| 2 | `[TODO]` | | | |
| 3 | `[TODO]` | | | |

> normalブロックで行パターンが異なる場合は、ブロックごとの個別設定欄に記載してください。

---

### 各normalブロック個別設定

ブロックごとの敵構成・シーケンスを記入してください。
敵構成が全ブロック共通の場合は「共通」と記載し、下記テンプレートを1ブロック分のみ埋めてください。

---

#### normal_00001

> シーケンスルール: フェーズ切り替えなし / SummonEnemy のみ / デフォルトグループのみ

| 行 | 出現条件 | 敵（MstEnemyStageParameter ID） | 数 | 召喚位置 | aura_type |
|----|---------|-------------------------------|---|---------|-----------|
| 1 | `ElapsedTime(250)` | `[TODO]` | `[TODO]` | ランダム | `Default` |
| 2 | `ElapsedTime(1500)` | `[TODO]` | `[TODO]` | ランダム | `Default` |
| 3 | `ElapsedTime(3000)` | `[TODO]` | `[TODO]` | ランダム | `Default` |（任意）

**ステータス係数**

| 敵（日本語名 / color / kind） | base_hp | hp_coef | 実HP（目安） | base_atk | atk_coef | 実ATK（目安） | defeated_score |
|--------------------------|---------|---------|------------|---------|---------|-------------|----------------|
| | | | | | | | |

---

#### normal_00002

| 行 | 出現条件 | 敵（MstEnemyStageParameter ID） | 数 | 召喚位置 | aura_type |
|----|---------|-------------------------------|---|---------|-----------|
| 1 | `ElapsedTime(250)` | `[TODO]` | `[TODO]` | ランダム | `Default` |
| 2 | `ElapsedTime(1500)` | `[TODO]` | `[TODO]` | ランダム | `Default` |
| 3 | `ElapsedTime(3000)` | `[TODO]` | `[TODO]` | ランダム | `Default` |（任意）

**ステータス係数**

| 敵（日本語名 / color / kind） | base_hp | hp_coef | 実HP（目安） | base_atk | atk_coef | 実ATK（目安） | defeated_score |
|--------------------------|---------|---------|------------|---------|---------|-------------|----------------|
| | | | | | | | |

---

#### normal_00003（以降、必要な分コピーして追加）

| 行 | 出現条件 | 敵（MstEnemyStageParameter ID） | 数 | 召喚位置 | aura_type |
|----|---------|-------------------------------|---|---------|-----------|
| 1 | `ElapsedTime(250)` | `[TODO]` | `[TODO]` | ランダム | `Default` |
| 2 | `ElapsedTime(1500)` | `[TODO]` | `[TODO]` | ランダム | `Default` |
| 3 | `ElapsedTime(3000)` | `[TODO]` | `[TODO]` | ランダム | `Default` |（任意）

**ステータス係数**

| 敵（日本語名 / color / kind） | base_hp | hp_coef | 実HP（目安） | base_atk | atk_coef | 実ATK（目安） | defeated_score |
|--------------------------|---------|---------|------------|---------|---------|-------------|----------------|
| | | | | | | | |

---

## 召喚アニメーション

| 設定 | 値 |
|------|-----|
| summon_animation_type（全体デフォルト） | `[TODO: None / ...]` |

---

## 特記事項・バランス設計メモ

```
[TODO: 以下の観点を参考に記入]

- normalブロック全体の難度曲線（序盤→後半でhp_coefをどう上げるか）
- bossブロックの初期配置演出（InitialSummonの狙い）
- 属性カラーの配置方針（ブロックごとの色テーマ）
- 雑魚の体数設計の考え方
```

---

## ガードレール確認（設計書完成時チェック）

- [ ] コマ行数: boss=1行、normal=3行 で固定（変更不可）
- [ ] 砦HP: boss=1,000、normal=100 で固定（変更不可）
- [ ] is_damage_invalidation: 空（ダメージ有効）
- [ ] フェーズ切り替え（SwitchSequenceGroup）未使用
- [ ] action_type は SummonEnemy のみ
- [ ] MstInGame.id = sequence_set_id = MstPage.id = MstEnemyOutpost.id が一致している
- [ ] MstAutoPlayerSequence.action_value に設定したIDが MstEnemyStageParameter に存在する
