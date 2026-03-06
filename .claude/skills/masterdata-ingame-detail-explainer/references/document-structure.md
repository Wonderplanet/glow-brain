# ドキュメント構成テンプレート（必須8セクション）

生成ドキュメントは以下の8セクションを**この順序で必ず含める**。

---

## セクション1: 概要（ファイル冒頭）

```markdown
# {INGAME_ID} インゲームデータ詳細解説

> 参照リポジトリ: `projects/glow-masterdata`
> リリースキー: {RELEASE_KEY}
> 本ファイルはMstAutoPlayerSequenceが{N}行の{コンテンツタイプ}の全データ設定を解説する

---

## 概要

{第1段落: シリーズ・コンテンツ種別・砦設定・BGM・コマ構成・コマ効果。「〇〇シリーズの△△コンテンツ（XXXブロック）。砦HPは{hp}でダメージ有効/無効。BGMは`{bgm_asset_key}`。{N}行構成のコマフィールドを使用し、コマ効果は{効果の概要}」などの形式で記述。}

{第2段落: 登場する敵の種類・数・各敵の特徴を抽象表現で記述。「{N}種類の敵が登場する。メイン敵の`{kind}`は{高耐久/標準/控えめ}・{高火力/攻撃控えめ}・{高速/中速/低速}な特性を持ち、{特徴的な行動パターンや役割}。サブ敵は{特徴}」などの形式で記述。}

{第3段落: グループ数・遷移条件・各グループの難度感を抽象表現で記述。「デフォルト + {N}つのウェーブグループで構成される。{遷移条件の概要（撃破数/時間/砦HP）}でグループが切り替わり、{ループ有無と起点}。序盤は{難度感}、中盤以降は{難度感}に変化する」などの形式で記述。}

{第4段落: result_tips / description の要点。「バトルヒントは「{result_tipsの要約}」。ステージ説明は「{descriptionの要約}」」などの形式で記述。}
```

> **抽象表現ガイドライン（数値→表現変換）**
>
> | 項目 | 数値範囲 | 表現例 |
> |------|---------|--------|
> | hp倍率 | ≤ 1.0 | 「控えめ」「弱め」 |
> | hp倍率 | 1.0〜3 | 「やや強め」「標準」 |
> | hp倍率 | 3〜10 | 「強め」「高耐久」 |
> | hp倍率 | 10〜30 | 「非常に高耐久」「かなり高耐久」 |
> | hp倍率 | 30超 | 「突出した耐久力」 |
> | atk倍率 | ≤ 1.0 | 「攻撃控えめ」 |
> | atk倍率 | 1〜5 | 「高火力」 |
> | atk倍率 | 5〜15 | 「かなり高火力」 |
> | atk倍率 | 15超 | 「非常に高火力」 |
> | 速度 | ≤ 28 | 「低速」「ゆっくり」 |
> | 速度 | 29〜34 | 「中速」 |
> | 速度 | 35〜40 | 「高速」 |
> | 速度 | 40超 | 「非常に高速」「最速クラス」 |
>
> 概要セクションは箇条書きを使わず、散文4段落・500文字程度でまとめる。
> 記述例は [overview-examples.md](overview-examples.md) を参照。

---

## セクション2: 関連テーブル設定

### MstInGame

| カラム | 値 |
|--------|-----|
| `id` | `{INGAME_ID}` |
| `mst_auto_player_sequence_set_id` | `{値}` |
| `bgm_asset_key` | `{値}` |
| `boss_bgm_asset_key` | `{値}` |
| `mst_page_id` | `{値}` |
| `mst_enemy_outpost_id` | `{値}` |
| `boss_mst_enemy_stage_parameter_id` | `{値}` |
| `normal_enemy_hp_coef` | `{値}` |
| `normal_enemy_attack_coef` | `{値}` |
| `normal_enemy_speed_coef` | `{値}` |
| `boss_enemy_hp_coef` | `{値}` |
| `boss_enemy_attack_coef` | `{値}` |
| `boss_enemy_speed_coef` | `{値}` |

### MstEnemyOutpost（敵砦）

| カラム | 値 | 意味 |
|--------|-----|------|
| `id` | `{値}` | |
| `hp` | `{値}` | {HP説明} |
| `is_damage_invalidation` | `{値}` | **{ダメージ有効/無効}** |
| `artwork_asset_key` | `{値}` | 背景アートワーク |

### MstPage + MstKomaLine（コマフィールド）

{行数}行構成。

```
row={N}  height={h}  layout={l}  ({N}コマ: {幅...})
  koma{N}: {asset}  width={w}  effect={効果}
```

> **コマ効果の補足**: {target説明}。

### MstInGameI18n（バトル説明文）

**result_tips（バトルヒント）:**
> {テキスト}

**description（ステージ説明）:**
> {テキスト}

---

## セクション3: 使用する敵パラメータ（MstEnemyStageParameter）一覧

{N}種類の敵パラメータを使用。`c_` プレフィックスはキャラ個別ID、`e_` は汎用敵。
IDの命名規則: `{c_/e_}{キャラID}_{コンテンツID}_{kind}_{color}`

### カラム解説

| カラム名（略称） | DBカラム名 | 説明 |
|---------------|-----------|------|
| id | id | MstEnemyStageParameterの主キー |
| キャラID | mst_enemy_character_id | 紐付くキャラモデル・スキルの参照元 |
| kind | character_unit_kind | `Normal`（通常敵）/ `Boss`（ボス）。UIオーラ表示に影響 |
| role | role_type | 属性相性の役職（Attack/Technical/Defense/Support） |
| color | color | 属性色（Red/Yellow/Green/Blue/Colorless） |
| sort_order | sort_order | ゲーム内表示順 |
| base_hp | hp | ベースHP（`enemy_hp_coef` 乗算前の素値） |
| base_atk | attack_power | ベース攻撃力（`enemy_attack_coef` 乗算前の素値） |
| base_spd | move_speed | 移動速度（数値が大きいほど速い） |
| well_dist | well_distance | 攻撃射程（コマ単位） |
| combo | attack_combo_cycle | 攻撃コンボ数（1=単発） |
| knockback | damage_knock_back_count | 被攻撃時ノックバック回数（0=ノックバックなし） |
| ability | mst_unit_ability_id1 | 特殊アビリティID |
| drop_bp | drop_battle_point | 基本ドロップバトルポイント |

### 全{N}種類の詳細パラメータ

> **日本語名カラムはStep 2-6（MstEnemyCharacterI18n）で取得した名称を必ず記載する。IDのまま記載しない。**
> **abilityカラムはStep 2-7（MstAbilityI18n）で取得した日本語説明を記載する。アビリティIDのまま記載しない。**

| MstEnemyStageParameter ID | 日本語名 | キャラID | kind | role | color | sort | base_hp | base_atk | base_spd | well_dist | combo | knockback | ability | drop_bp |
|--------------------------|---------|---------|------|------|-------|------|---------|---------|---------|-----------|-------|-----------|---------|---------|
| ... |

> **実際のHP・ATKは `base × MstAutoPlayerSequence.enemy_hp_coef` で決まる。**

### 敵パラメータの特性解説

{ボスと雑魚の比較表・特筆すべき設計上の特徴を記載}

---

## セクション4: グループ構造の全体フロー（Mermaid）

> **レイアウト**: `flowchart TD`（上下方向）を使用する。`flowchart LR`（横方向）はノードラベルが長いと見切れるため使用しない。
>
> **ノードラベルのルール**:
> - グループID（DEF / w1 / w2...）を冒頭に表示
> - 主要な敵は**Step 2-6で取得した日本語名**を使用する（キャラIDのまま記載しない）
> - Boss種・通常種の区別を括弧で補足（例: `桐馬（Boss）` / `門神×4体`）
> - 詳細（elem番号・召喚数・コマ侵入など）は省略してセクション5に委ねる
>
> **Mermaid記述ルール（必須）**:
> - 改行は `<br/>` を使用する（`\n` はレンダラーによって文字として表示される）
> - 絵文字はノードラベルに含めない（レンダリングが崩れる場合がある）
> - ひし形ノード `{}` は使用しない → 長方形 `[]` で代替する（`{}` 内では `<br/>` が動作しないため）
> - `style` には必ず `color:#fff` を明示する（背景色によっては文字が見えなくなる）
> - ノードラベルは3行以内に収める（見切れ防止）

```mermaid
flowchart TD
    START([バトル開始]) --> DEF

    DEF["**DEF**
{主要敵の日本語名（種別）}"]
    DEF -- "{条件}" --> W1

    W1["**w1**
{主要敵の日本語名（種別）}"]
    W1 -- "{条件}" --> W2
    ...

    style DEF fill:#6b7280,color:#fff
    style W1 fill:#3b82f6,color:#fff
    style W2 fill:#3b82f6,color:#fff
    style W3 fill:#f59e0b,color:#fff
    style W4 fill:#f59e0b,color:#fff
    style W5 fill:#ef4444,color:#fff
    style W6 fill:#8b5cf6,color:#fff
```

> **Mermaid スタイルカラー規則**:
> - デフォルトグループ: `#6b7280`（グレー）
> - w1〜w2: `#3b82f6`（青）
> - w3〜w4: `#f59e0b`（橙）
> - w5以降: `#ef4444`（赤）
> - ループ起点直前のグループ: `#8b5cf6`（紫）

---

## セクション5: 全{N}行の詳細データ（グループ単位）

各グループのデータを以下の形式でまとめる。
**全グループ分を省略なく記載すること。**

### {グループ名}グループ（elem {n}〜{n}, groupchange_{n}）

{グループ概説}

| id | elem | 条件 | アクション | 召喚数 | interval | aura | hp倍 | atk倍 | override_bp | 説明 |
|----|------|------|-----------|--------|---------|------|------|------|------------|------|
| ... |

**ポイント:**
- {注目すべき設定の解説}

---

## セクション6: グループ切り替えまとめ表

| 切り替え | 条件 | 遷移先 |
|---------|------|--------|
| デフォルト → w1 | **FriendUnitDead({n})** | w1 |
| w1 → w2 | **FriendUnitDead({n})** | w2 |
| ... |

各グループで倒すべき目安:
- デフォルト: {n}体
- w1: {n}体
- ...

---

## セクション7: スコア体系

バトルポイントは `override_drop_battle_point`（MstAutoPlayerSequence設定値）が優先される。

| 敵の種類 | override_bp（獲得バトルポイント） | 備考 |
|---------|----------------------------------|------|
| ... |

---

## セクション8: この設定から読み取れる設計パターン

### 1. {パターン名}
{説明}

### 2. {パターン名}
{説明}

{3〜6項目記載。具体的なデータを引用し、設計意図を読み解く}
```

---

## 留意事項

- セクション5の詳細データは**全行省略なし**で記載する（DuckDBで取得した全行を反映）
- セクション8の設計パターンは最低3項目、最大6項目
- 数値は3桁区切りカンマ（例: `100,000`）で統一
- condition_typeの値はそのまま記載（例: `GroupActivated(300)` = 3,000ms = 3秒）
