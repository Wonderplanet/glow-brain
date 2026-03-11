# VD シーケンスパターン詳細

限界チャレンジ（VD）の boss / normal ブロック別のMstAutoPlayerSequence設計パターン。

---

## 共通ルール

- `action_type` は **`SummonEnemy` のみ** で構成する
- **`SwitchSequenceGroup` は使用禁止**（フェーズ切り替えなし）
- `sequence_group_id` は **空**（デフォルトグループのみ）
- `koma_effect_type` は **`None` 固定**

### c_キャラ（プレイアブルキャラが敵として出現）の制約

- `action_value` が `c_` で始まるキャラは **同一トリガーで `summon_count >= 2` かつ `summon_interval = 0` を禁止**（瞬間同時複数召喚）
  - 世界観的に同一プレイアブルキャラが複数体同時にフィールドに存在する状態は矛盾が生じる
  - 実データでも `summon_interval=0` かつ `summon_count>=2` のc_キャラ召喚は 0件
- 撃破後に再出撃させる場合は **`FriendUnitDead`（累積撃破数トリガー）** を使用する（実データ183件・最多パターン）
  - `condition_value=1`（1体倒すごとに即時再召喚）が最基本パターン

> 参考: `domain/tasks/20260310_115400_vd_ingame_masterdata_generation/knowledge/MstAutoPlayerSequence_c_キャラ召喚パターン分析.md`

---

## bossブロック（vd_boss）シーケンスパターン

### 行数目安: 2〜4行

### 基本パターン

```
行1: InitialSummon → SummonEnemy(ボス) × 1
     summon_position=1.7（ゲート付近）
     move_start_condition_type=Damage, move_start_condition_value=1
     aura_type=Boss

行2: ElapsedTime(500) → SummonEnemy(雑魚A) × N体
     aura_type=Default

行3: ElapsedTime(3000) → SummonEnemy(雑魚A) × N体  ※任意
     aura_type=Default
```

### 設計ポイント

- ボスは `InitialSummon` でゲート付近（`summon_position=1.7`）に配置
- ボスは1ダメージ受けたら移動開始（`move_start_condition_type=Damage, move_start_condition_value=1`）
- ボス撃破まで敵ゲートはダメージ無効（`MstEnemyOutpost.is_damage_invalidation` は空=ダメージ有効だが、ゲーム内ロジックでボス撃破前は無効）
- 雑魚は `ElapsedTime` で時間差出現

### ID命名例（`vd_kai_boss_00001` の場合）

```
行1: id=vd_kai_boss_00001_1, sequence_element_id=1
行2: id=vd_kai_boss_00001_2, sequence_element_id=2
行3: id=vd_kai_boss_00001_3, sequence_element_id=3
```

---

## normalブロック（vd_normal）シーケンスパターン

### 行数目安: 3〜8行

### 基本パターン

```
行1: ElapsedTime(250) → SummonEnemy(雑魚A) × N体
     aura_type=Default

行2: ElapsedTime(1500) → SummonEnemy(雑魚B) × N体
     aura_type=Default

行3: ElapsedTime(3000) → SummonEnemy(雑魚A) × N体  ※任意
     aura_type=Default
```

### 設計ポイント

- ボスなし（`MstInGame.boss_mst_enemy_stage_parameter_id` は空）
- 全行 `aura_type=Default`
- 雑魚を時間差で出現させる構成
- フェーズ切り替えなし
- **最低15体以上**の雑魚が登場するよう設計する

### ID命名例（`vd_kai_normal_00001` の場合）

```
行1: id=vd_kai_normal_00001_1, sequence_element_id=1
行2: id=vd_kai_normal_00001_2, sequence_element_id=2
行3: id=vd_kai_normal_00001_3, sequence_element_id=3
```

---

## 重要カラム一覧

| カラム | bossブロック | normalブロック |
|--------|------------|--------------|
| `sequence_set_id` | `vd_{作品ID}_boss_{連番}` | `vd_{作品ID}_normal_{連番}` |
| `sequence_group_id` | 空（デフォルトグループ） | 空（デフォルトグループ） |
| `condition_type`（行1） | `InitialSummon`（ボス行） | `ElapsedTime` |
| `condition_value`（行1） | `0` または `1` | `250` |
| `action_type` | `SummonEnemy` | `SummonEnemy` |
| `action_value` | MstEnemyStageParameter.id | MstEnemyStageParameter.id |
| `summon_count` | 1（ボス）/ N（雑魚） | N |
| `summon_position`（ボス行） | `1.7` | — |
| `move_start_condition_type`（ボス行） | `Damage` | — |
| `move_start_condition_value`（ボス行） | `1` | — |
| `aura_type`（ボス行） | `Boss` | — |
| `aura_type`（雑魚行） | `Default` | `Default` |
| `enemy_hp_coef` | `1`（デフォルト） | `1`（デフォルト） |
| `enemy_attack_coef` | `1`（デフォルト） | `1`（デフォルト） |
| `enemy_speed_coef` | `1`（デフォルト） | `1`（デフォルト） |

---

## ElapsedTime の代表値

| 値 | 秒換算 | 用途 |
|----|-------:|------|
| `250` | 0.25秒 | 最初の雑魚出現（normalブロック） |
| `500` | 0.5秒 | 2波目（bossブロックの雑魚） |
| `1500` | 1.5秒 | 2波目（normalブロック） |
| `3000` | 3.0秒 | 3波目以降 |

---

## summon_interval の使い方

同一行で複数体を時間差で出す場合に設定する:

```
summon_count=5, summon_interval=1500 → 5体を1.5秒間隔で順次出現
```
