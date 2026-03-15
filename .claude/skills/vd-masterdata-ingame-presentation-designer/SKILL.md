---
name: vd-masterdata-ingame-presentation-designer
description: VDインゲーム設計書（design.md）の「演出」セクション（背景・BGM・オーラ・召喚アニメーション）を生成・更新するスキル。VD固定値の自動適用と作品別ループ背景の設定を行います。「VD演出設計」「BGM設定」「背景設定」「presentation-design」などのキーワードで使用します。
---

# VD演出・アセット設計スキル

## 概要

VDインゲーム設計書（design.md）の **`## 演出`** セクションを生成・更新する専門スキル。

- **担当セクション**: `## 演出`（背景・BGM・オーラ・召喚アニメーション）
- VD固定値の自動適用と作品別アセットの設定を行う

---

## 入力引数

| 引数 | 必須 | 説明 |
|------|------|------|
| `作品ID` | ✓ | kai / dan / spy 等 |
| `ブロック種別` | ✓ | normal または boss |
| `キャラリスト` | ✓ | aura_type設定のため（c_キャラ・ボス有無の確認） |
| `[--batch]` | 任意 | 確認ループをスキップ |

---

## VD固定値（変更不可）

| 項目 | bossブロック | normalブロック |
|------|------------|--------------|
| `bgm_asset_key` | `SSE_SBG_003_004` | `SSE_SBG_003_010` |
| `boss_bgm_asset_key` | `""`（空文字） | `""`（空文字） |
| `mst_auto_player_sequence_id` | `""`（空文字） | `""`（空文字） |
| `mst_defense_target_id` | `__NULL__` | `__NULL__` |
| 全coefカラム×6 | `1.0` | `1.0` |

---

## 3ステップワークフロー

### Step 0: 準備・ドキュメント読み込み

以下を読み込む。

**参照ファイル（必須）**:
- `.claude/skills/vd-masterdata-ingame-design-creator/references/series-koma-assets.csv` — 作品別アセット情報
- `.claude/skills/vd-masterdata-ingame-design-creator/references/vd-column-defaults.md` — デフォルト値（loop_background_asset_key等）

### Step 1: 演出設定の決定

#### BGM設定

| ブロック種別 | bgm_asset_key |
|------------|--------------|
| normal | `SSE_SBG_003_010` |
| boss | `SSE_SBG_003_004` |

#### loop_background_asset_key（ループ背景）

`vd-column-defaults.md` の `loop_background_asset_key 作品別設定値` を参照:

| 種別 | 作品 | 設定値 |
|------|------|-------|
| Normal例外 | jig | `jig_00002` |
| Normal例外 | mag | `mag_00004` |
| Boss | kai | `kai_00001` |
| Boss | dan | `dan_00001` |
| その他Normal | 全作品 | `""`（空文字） |
| その他Boss | 未定作品 | `""`（空文字） |

#### 敵キャラオーラ（aura_type）

各キャラの役割に応じてaura_typeを設定:

| キャラ役割 | aura_type | 適用例 |
|-----------|---------|--------|
| 雑魚（e_キャラ）| `Default` | 通常の雑魚敵 |
| c_キャラ（フレンドユニット系）| `Default` | フレンドキャラ系 |
| ボス | `Boss` | bossブロックのボス |
| 降臨ボス1 | `AdventBoss1` | 特殊ボス演出1 |
| 降臨ボス2 | `AdventBoss2` | 特殊ボス演出2 |

VDでは原則:
- 雑魚: `Default`
- ボス（`boss_mst_enemy_stage_parameter_id` に設定されるキャラ）: `Boss`

#### 召喚アニメーション（summon_animation_type）

VDでは全エントリ `None`（固定）。

### Step 2: 演出セクション生成

以下のフォーマットでMarkdownを生成する。

```markdown
## 演出

### アセット

#### 背景
| 設定箇所 | アセットキー | 備考 |
|---------|------------|------|
| loop_background_asset_key | {値} | {ブロック種別}・{作品ID}用ループ背景 |

#### BGM
| 設定 | 値 | 備考 |
|-----|---|------|
| bgm_asset_key | {値} | VD {ブロック種別}固定値 |
| boss_bgm_asset_key | "" | VD全ブロック固定（空文字） |

---

### 敵キャラオーラ
| オーラ種別 | 使用箇所 |
|----------|---------|
| Default | 雑魚キャラ（{雑魚キャラID一覧}） |
| Boss | ボスキャラ（{ボスキャラID}） |

---

### 敵キャラ召喚アニメーション
VD全ブロック共通: `summon_animation_type=None`（召喚アニメーションなし）
```

### Step 3: 確認・更新

`--batch` フラグがない場合:
```
演出・アセット設定を生成しました。内容をご確認ください。

修正がなければ「OK」または「承認」とお伝えください。
修正がある場合は具体的にご指示ください。
```

承認後（または `--batch` 時）、design.md の該当セクションを更新する。

---

## ガードレール

1. **bgm_asset_key はブロック種別で固定**: normal=`SSE_SBG_003_010`、boss=`SSE_SBG_003_004`
2. **boss_bgm_asset_key は常に空文字**: VD全ブロック共通
3. **loop_background_asset_key は vd-column-defaults.md を参照**: 作品・ブロック種別に応じた正しい値を設定
4. **summon_animation_type は全エントリ `None`**: VD固定

---

## リファレンス

- `.claude/skills/vd-masterdata-ingame-design-creator/references/series-koma-assets.csv` — 作品別アセットキー
- `.claude/skills/vd-masterdata-ingame-design-creator/references/vd-column-defaults.md` — デフォルト値（loop_background_asset_key等）
- `domain/knowledge/masterdata/table-docs/MstInGame.md` — テーブル定義
