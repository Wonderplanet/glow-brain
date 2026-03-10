# 限界チャレンジ（vd）マスタデータ要件

限界チャレンジのインゲームマスタデータ作成要件をまとめたドキュメントです。

---

## 概要

- **コンテンツ種別**: `vd`
- **IDプレフィックス**: `vd_{シリーズ}_boss_{連番}` / `vd_{シリーズ}_normal_{連番}`
- **構成単位**: 「ブロック」という単位でインゲームを構成する。`MstInGame.id` 1件 = 1ブロック
- **作品単位の構成**: 1作品につき **boss 1個 + normal N個** のペアで構成する
- **複数ブロックを連結**して長いコンテンツとして運用する

---

## ブロック種別

| ブロック種別 | ステージ種別 | 動作仕様 |
|------------|------------|---------|
| **bossブロック** | `vd_boss` | 敵ゲート前にボスが召喚され、N秒おきに進軍する能動的な構成。ボス撃破まで敵ゲートはダメージ無効 |
| **normalブロック** | `vd_normal` | フロア毎に配置された敵が、味方キャラが該当フロアに進行した際に起動する受動的な構成 |

---

## 固定値一覧

### MstEnemyOutpost HP（固定）

| ブロック種別 | HP | 備考 |
|------------|----:|------|
| boss | `1,000` | ボスブロック固定値 |
| normal | `100` | 通常ブロック固定値 |

- `is_damage_invalidation`: 空（ダメージ有効）

### MstKomaLine 行数（固定）

| ブロック種別 | コマ行数 |
|------------|--------:|
| boss | **1行**（固定） |
| normal | **3行**（固定） |

---

## ID命名規則

```
vd_{シリーズ}_{ブロック種別}_{連番5桁}
```

| ブロック種別 | パターン | 例 |
|------------|---------|-----|
| ボスブロック | `vd_{シリーズ}_boss_{連番}` | `vd_kai_boss_00001` |
| 通常ブロック | `vd_{シリーズ}_normal_{連番}` | `vd_kai_normal_00001` |

- `{シリーズ}` はキャラシリーズ略称（`kai` / `dan` / `spy` 等）
- インゲームID・MstAutoPlayerSequence.sequence_set_id・MstPage.id・MstEnemyOutpost.id はすべて同一値を使用する

### MstEnemyStageParameter.id の短縮形

| ケース | ID短縮形 |
|--------|---------|
| vd（boss/normalで共通） | `{シリーズ}_vd` |

例: `c_kai_00201_kai_vd_Boss_Red`（ボス）/ `e_kai_00001_kai_vd_Normal_Colorless`（雑魚）

---

## 生成対象テーブル（ブロックごと）

| テーブル | 必須度 | 内容 |
|---------|--------|------|
| `MstEnemyStageParameter` | **必須** | 敵パラメータ（ボス・雑魚分） |
| `MstEnemyOutpost` | **必須** | HP固定値（boss=1,000 / normal=100） |
| `MstPage` | **必須** | バトルフィールドのページID |
| `MstKomaLine` | **必須** | コマライン（boss=1行 / normal=3行） |
| `MstAutoPlayerSequence` | **必須** | 敵出現シーケンス |
| `MstInGame` | **必須** | インゲーム全体設定 |

---

## MstAutoPlayerSequence シーケンス設計

### 行数目安

| ブロック種別 | シーケンス行数目安 |
|------------|----------------:|
| boss | `2 〜 4行` |
| normal | `3 〜 8行` |

### シーケンス設定ルール

- **フェーズ切り替えはしない**（`SwitchSequenceGroup` は使用しない）
- `action_type` は `SummonEnemy` のみで構成する
- グループ（`sequence_group_id`）は空（デフォルトグループ）のみで設計する

### bossブロックの基本シーケンスパターン

```
行1: InitialSummon → SummonEnemy(ボス) × 1
     summon_position=1.7（砦付近）
     move_start_condition_type=Damage, move_start_condition_value=1

行2: ElapsedTime(500) → SummonEnemy(雑魚A) × N体
行3: ElapsedTime(3000) → SummonEnemy(雑魚A) × N体  ※任意
```

- ボスは `InitialSummon` で砦付近（`summon_position=1.7`）に配置し、1ダメージ受けたら移動開始
- `aura_type` はボス=`Boss`、雑魚=`Default`

### normalブロックの基本シーケンスパターン

```
行1: ElapsedTime(250) → SummonEnemy(雑魚A) × N体
行2: ElapsedTime(1500) → SummonEnemy(雑魚B) × N体
行3: ElapsedTime(3000) → SummonEnemy(雑魚A) × N体  ※任意
```

- 雑魚を時間差で出現させる構成
- `aura_type` はすべて `Default`
- フェーズ切り替えはしないため `SwitchSequenceGroup` は不使用

---

## MstInGame 主要設定

| カラム | bossブロック | normalブロック |
|--------|------------|--------------|
| `content_type` | `Vd` | `Vd` |
| `stage_type` | `vd_boss` | `vd_normal` |
| `boss_mst_enemy_stage_parameter_id` | ボスパラメータID | 空（ボスなし） |

---

## コマ設計（MstKomaLine）

### bossブロック（1行固定）

```
row=1: height=1.0, koma1_width=1.0
```

### normalブロック（3行固定）

```
row=1: height 合計が1.0になるよう設定
row=2: ...
row=3: ...
```

- コマ幅合計は1行あたり必ず 1.0
- コマ効果（`koma_effect_type`）は指定がない場合 `None`
- `koma1_effect_target_side` はエフェクトなしでも `All` を設定する

---

## 作成時に確認する項目

vd_boss / vd_normal を作成する場合、以下を確認する:

| 項目 | 内容 |
|------|------|
| シリーズID | シリーズ略称（例: kai, dan, spy） |
| ブロック構成 | boss 1個 + normal N個の総ブロック数 |
| 各ブロックの連番 | boss_00001, normal_00001〜000XX |
| bossブロックの敵構成 | ボスキャラID・色属性・雑魚の種類と数 |
| normalブロックの敵構成 | 各ブロックの雑魚キャラID・色属性・体数（ブロックごとに異なる場合は個別確認） |

**固定値の周知:**
- vd_boss: MstEnemyOutpost HP = **1,000** / コマ行数 = **1行** （固定）
- vd_normal: MstEnemyOutpost HP = **100** / コマ行数 = **3行** （固定）

---

## 設計確認サマリーテンプレート

```markdown
## 設計確認サマリー（限界チャレンジ）

### 作成するブロック一覧

| ブロック種別 | インゲームID | コマ行数 | アウトポストHP |
|------------|-----------|--------:|--------------:|
| vd_boss | `vd_{シリーズ}_boss_{連番}` | 1行 | 1,000 |
| vd_normal | `vd_{シリーズ}_normal_{連番}` | 3行 | 100 |
...（ブロック数分繰り返し）

### ボスブロック（vd_boss）の敵構成
| 種別 | キャラID | 色 |
|------|---------|-----|
| ボス | `{キャラID}` | {色} |
| 雑魚A | `{キャラID}` | {色} |

### 通常ブロック（vd_normal）の敵構成
| ブロック | 雑魚キャラID | 色 | 体数 |
|---------|-------------|-----|----:|
| normal_00001 | `{キャラID}` | {色} | {N} |
| normal_00002 | `{キャラID}` | {色} | {N} |

### シーケンス設計方針
- フェーズ切り替え（SwitchSequenceGroup）は使用しない
- SummonEnemy のみで構成するシンプルな設計

### 生成するCSVファイル（ブロックごと）
各ブロックにつき:
1. MstEnemyStageParameter（敵数分の行）
2. MstEnemyOutpost（1行、HP固定値）
3. MstPage（1行）
4. MstKomaLine（boss=1行 / normal=3行）
5. MstAutoPlayerSequence（シーケンス行数分）
6. MstInGame（1行）
```

---

## ガードレール

1. **コマ行数は変更不可**: boss=1行、normal=3行は固定値。指定があっても変更しない
2. **アウトポストHPは変更不可**: boss=1,000、normal=100は固定値
3. **フェーズ切り替え禁止**: `SwitchSequenceGroup` は使用しない。シーケンスはデフォルトグループ（`sequence_group_id` 空）のみで設計する
4. **IDの一貫性**: MstInGame.id = MstAutoPlayerSequence.sequence_set_id = MstPage.id = MstEnemyOutpost.id
5. **MstEnemyStageParameterのFK確認**: MstAutoPlayerSequence.action_value（SummonEnemy時）に設定するIDが、同バッチ内のMstEnemyStageParameterに存在することを確認する

---

## 参照先

| パス | 用途 |
|-----|------|
| `domain/knowledge/masterdata/table-docs/MstAutoPlayerSequence.md` | シーケンス詳細仕様 |
| `domain/knowledge/masterdata/table-docs/MstEnemyStageParameter.md` | 敵パラメータ詳細仕様 |
| `domain/knowledge/masterdata/table-docs/MstPage.md` | ページ設計詳細 |
| `domain/knowledge/masterdata/table-docs/MstKomaLine.md` | コマライン設計詳細 |
| `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` | 敵砦設計詳細 |
