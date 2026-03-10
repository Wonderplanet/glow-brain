# 限界チャレンジ（vd）normalブロック マスタデータ要件

限界チャレンジの **normalブロック（`vd_normal`）** に特化したインゲームマスタデータ作成要件ドキュメントです。

---

## 概要

- **コンテンツ種別**: `vd`
- **ブロック種別**: `Normal`
- **IDプレフィックス**: `vd_{作品ID}_normal_{連番5桁}`
- **構成単位**: 1作品につき **normal N個**（boss 1個とペアで運用）

### 動作仕様

フロア毎に配置された敵が、味方キャラが該当フロアに進行した際に起動する **受動的な構成**。

---

## 固定値

| 項目 | 値 | 備考 |
|------|---:|------|
| `MstEnemyOutpost` HP | `100` | 固定値（変更不可） |
| `MstKomaLine` 行数 | **3行** | 固定値（変更不可） |
| `MstEnemyOutpost.is_damage_invalidation` | 空 | ダメージ有効 |

---

## ID命名規則

```
vd_{作品ID}_normal_{連番5桁}
```

**例**: `vd_kai_normal_00001`

- `{作品ID}` はキャラシリーズ略称（`kai` / `dan` / `spy` 等）
- MstInGame.id・MstAutoPlayerSequence.sequence_set_id・MstPage.id・MstEnemyOutpost.id はすべて同一値を使用する

### MstEnemyStageParameter.id の短縮形

| ケース | ID短縮形 |
|--------|---------|
| vd（boss/normalで共通） | `{作品ID}_vd` |

**例**: `e_kai_00001_kai_vd_Normal_Colorless`

---

## 生成対象テーブル

| テーブル | 必須度 | 内容 |
|---------|--------|------|
| `MstEnemyStageParameter` | **必須** | 敵パラメータ（雑魚分） |
| `MstEnemyOutpost` | **必須** | HP = 100（固定） |
| `MstPage` | **必須** | バトルフィールドのページID |
| `MstKomaLine` | **必須** | コマライン（3行固定） |
| `MstAutoPlayerSequence` | **必須** | 敵出現シーケンス |
| `MstInGame` | **必須** | インゲーム全体設定 |

---

## MstAutoPlayerSequence シーケンス設計

### 行数目安

`3 〜 8行`

### シーケンス設定ルール

- **フェーズ切り替えはしない**（`SwitchSequenceGroup` は使用しない）
- `action_type` は `SummonEnemy` のみで構成する
- グループ（`sequence_group_id`）は空（デフォルトグループ）のみで設計する

### 基本シーケンスパターン

```
行1: ElapsedTime(250) → SummonEnemy(雑魚A) × N体
行2: ElapsedTime(1500) → SummonEnemy(雑魚B) × N体
行3: ElapsedTime(3000) → SummonEnemy(雑魚A) × N体  ※任意
```

- 雑魚を時間差で出現させる構成
- `aura_type`: すべて `Default`
- フェーズ切り替えはしないため `SwitchSequenceGroup` は不使用

---

## MstInGame 主要設定

| カラム | 値 |
|--------|-----|
| `content_type` | `Dungeon` |
| `stage_type` | `vd_normal` |
| `boss_mst_enemy_stage_parameter_id` | 空（ボスなし） |

---

## コマ設計（MstKomaLine）

**3行固定**:

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

| 項目 | 内容 |
|------|------|
| 作品ID | 作品ID（例: kai, dan, spy） |
| 連番 | `normal_00001〜000XX`（ブロック数分） |
| 各ブロックの雑魚構成 | ブロックごとの雑魚キャラID・色属性・体数（ブロックごとに異なる場合は個別確認） |

---

## ガードレール

1. **コマ行数は変更不可**: 3行固定。指定があっても変更しない
2. **アウトポストHPは変更不可**: 100固定
3. **フェーズ切り替え禁止**: `SwitchSequenceGroup` は使用しない
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
| `domain/knowledge/masterdata/table-docs/MstEnemyOutpost.md` | 敵ゲート設計詳細 |
