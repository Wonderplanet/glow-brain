# MstEnemyStageParameter ↔ MstAttack / MstAttackElement リレーション

> 調査日: 2026-03-13
> 参照コード: `projects/glow-client/.../MasterDataRepository.cs`, `EnemyStageParameterDataTranslator.cs`, `CharacterUnitFactory.cs`

---

## 1. 概要

`MstEnemyStageParameter` と `MstAttack` / `MstAttackElement` の間には **DB上の外部キーは存在しない**。
クライアント実装（C#）が**暗黙のリレーション**として結合している。

---

## 2. DB上のリレーション

```
MstEnemyStageParameter.id  <── 外部キーなし ──>  MstAttack.mst_unit_id
```

- `MstEnemyStageParameter` テーブルに `mst_attack_id` カラムは**存在しない**
- DBスキーマ（`master_tables_schema.json`）上は両テーブルは独立している

---

## 3. クライアント実装での結合ロジック

### 3-1. MasterDataRepository.cs

`CreateMstEnemyStageParameterModel()` 内で `MstEnemyStageParameter.id` を使って `MstAttack` を検索している。

```csharp
// MasterDataRepository.cs
private MstEnemyStageParameterModel CreateMstEnemyStageParameterModel(MasterDataId id)
{
    // id = MstEnemyStageParameter.id
    var normalAttack     = GetMstAttackModel(id, AttackKind.Normal);
    var specialAttack    = GetMstAttackModel(id, AttackKind.Special);
    var appearanceAttack = GetMstAttackModel(id, AttackKind.Appearance);

    // ... EnemyStageParameterDataTranslator に渡す
}

private MstAttackModel GetMstAttackModel(MasterDataId mstUnitId, AttackKind attackKind)
{
    var targetData = DataStore.Get<MstAttackData>()
        .FirstOrDefault(d =>
            d.MstUnitId == mstUnitId.Value &&   // ← MstAttack.mst_unit_id でフィルタ
            d.AttackKind == attackKind);

    if (targetData == null) return MstAttackModel.Empty;  // ← 見つからない場合は Empty
    // ... AttackDataTranslator で変換
}
```

**暗黙のリレーション式**:

```
MstAttack.mst_unit_id == MstEnemyStageParameter.id
```

### 3-2. クラス構造（変換の流れ）

```
MasterDataRepository
  ↓ CreateMstEnemyStageParameterModel(id)
  │  ├─ GetMstAttackModel(id, Normal)     → AttackData（or AttackData.Empty）
  │  ├─ GetMstAttackModel(id, Special)    → AttackData（or AttackData.Empty）
  │  └─ GetMstAttackModel(id, Appearance) → AttackData（or AttackData.Empty）
  ↓
EnemyStageParameterDataTranslator.ToEnemyStageParameterModel(
    normalAttack, specialAttack, appearanceAttack)
  ↓
MstEnemyStageParameterModel
  ├─ AttackData NormalAttack
  ├─ AttackData SpecialAttack
  └─ AttackData AppearanceAttack
  ↓
CharacterUnitFactory
  └─ CharacterUnitModel（インゲームで使用）
```

---

## 4. VD専用敵キャラの特性

### 4-1. VD敵IDの命名パターン

VD専用の敵キャラID（`MstEnemyStageParameter.id`）は以下の形式:

```
e_{キャラID}_vd_{ユニット種別}_{色}
c_{キャラID}_vd_{ユニット種別}_{色}

例:
  e_kai_00101_vd_Normal_Yellow
  e_kai_00101_vd_Boss_Yellow
  c_kai_00101_vd_Normal_Yellow
```

### 4-2. VD敵に対応するMstAttackは0件

`MstAttack.csv` を調査した結果、`_vd_` を含む `mst_unit_id` のレコードは **0件**。

| mst_unit_idのプレフィックス | 件数 |
|---|---|
| `c_`（フレンドキャラ系） | 1,273件 |
| `e_`（敵キャラ系） | 718件 |
| `chara_`（プレイヤーキャラ系） | 642件 |
| `_vd_` を含むもの | **0件** |

### 4-3. VD敵の攻撃動作

VD専用敵には `MstAttack` レコードが存在しないため、クライアントは `AttackData.Empty` を使用する。

```csharp
// MstAttack が見つからない場合
if (targetData == null) return MstAttackModel.Empty;
```

VD敵の基本的な攻撃動作は `MstEnemyStageParameter` の以下のカラムで制御される:

| カラム | 役割 |
|---|---|
| `attack_power` | 攻撃力（ダメージ量） |
| `attack_combo_cycle` | コンボ攻撃のサイクルフレーム数 |

**MstAttack / MstAttackElement は VD敵には不要**（現状の実装では使用されない）。

---

## 5. 既存キャラ（非VD）での利用パターン

VD以外の `e_` / `c_` / `chara_` キャラは、対応する `MstAttack` レコードが存在し、攻撃の詳細が定義される。

```
MstEnemyStageParameter.id = "e_kai_00101"
  ↓（暗黙のリレーション）
MstAttack.mst_unit_id = "e_kai_00101"
  ├─ attack_kind = Normal   → MstAttackElement（sort_order順のヒット判定）
  ├─ attack_kind = Special  → MstAttackElement（必殺技の詳細）
  └─ attack_kind = Appearance → MstAttackElement（登場演出）
```

---

## 6. まとめ

| 項目 | 内容 |
|---|---|
| DB外部キー | **なし**。両テーブルは独立 |
| 暗黙のリレーション | `MstAttack.mst_unit_id == MstEnemyStageParameter.id` |
| 結合タイミング | クライアント（C#）の `MasterDataRepository` が実行時に結合 |
| 見つからない場合 | `AttackData.Empty`（攻撃なし状態）が返る |
| **VD敵キャラ** | `_vd_` を含むIDには MstAttack が **0件** → 常に `AttackData.Empty` |
| VD敵の攻撃制御 | `MstEnemyStageParameter.attack_power` + `attack_combo_cycle` で代替 |
| VD敵CSVに追加不要 | `MstAttack` / `MstAttackElement` のVD敵用レコードは作成しなくてよい |
