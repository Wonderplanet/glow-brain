# MstEventBonusUnit 詳細説明

> CSVパス: `projects/glow-masterdata/MstEventBonusUnit.csv`

---

## 概要

`MstEventBonusUnit` は**イベント期間中に特定ユニットへ付与するステータスボーナスの設定テーブル**。

イベントごとに設けられる「イベントボーナスユニット」制度を管理する。特定の作品キャラクターをデッキに編成してイベントクエストをプレイすると、そのキャラクターのステータスが `bonus_percentage` で指定した割合分増加する。これによってイベント関連キャラクターの使用を促進し、ゲームの戦略性とガチャ誘導を兼ねる。

CSVの行数は85件（2026年3月現在）。

### ゲームプレイへの影響

- **`bonus_percentage`**: ユニットのステータスへのボーナス割合（%）。同一 `event_bonus_group_id` 内で各ユニットに異なる倍率を設定できる。例: 最新ガチャキャラに20%、既存キャラに5〜10%、旧キャラに3%など
- **`event_bonus_group_id`**: ボーナスグループのID。`MstQuestEventBonusSchedule` や `MstStageEventSetting` がこのIDを参照してボーナスを適用する対象クエストを決定する
- **`is_pick_up`**: ボーナスキャラの簡易表示フラグ（現在実データでは未使用、NULL）

### 関連テーブルとの構造図

```
MstEventBonusUnit（ボーナスユニット定義）
  └─ mst_unit_id → MstUnit.id（ボーナス対象のユニット）
  └─ event_bonus_group_id → MstQuestEventBonusSchedule.event_bonus_group_id
                            → MstStageEventSetting.event_bonus_group_id
                            （ボーナスが適用されるクエスト・ステージの紐付け）
```

---

## 全カラム一覧

### mst_event_bonus_units カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | int | 不可 | - | 主キー。1始まりの連番整数 |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_unit_id` | varchar(255) | 不可 | "" | ボーナス対象のユニットID（`mst_units.id`） |
| `bonus_percentage` | int | 不可 | - | ステータスボーナス割合（%）。3〜30程度の値が多い |
| `event_bonus_group_id` | varchar(255) | 不可 | "" | ボーナスグループID。クエスト側でこのIDを参照してボーナスを適用する |
| `is_pick_up` | tinyint unsigned | 不可 | - | ボーナスキャラ簡易表示の対象フラグ（現在実データでは未使用） |

---

## 命名規則 / IDの生成ルール

| 項目 | 規則 | 例 |
|------|------|----|
| `id` | 1始まりの連番整数 | `1`, `85` |
| `event_bonus_group_id` | `raid_{作品略称}{連番}_{5桁連番}` | `raid_kai_00001`, `raid_spy1_00001` |
| `mst_unit_id` | `chara_{作品略称}_{5桁コード}` | `chara_kai_00101`, `chara_sur_00501` |

### `event_bonus_group_id` のパターン

同一作品の複数回イベントでは `spy1`, `spy2` のように数字を付けて区別する：
- `raid_kai_00001` → 怪獣8号 第1回
- `raid_spy1_00001` → SPY×FAMILY 第1回
- `raid_spy2_00001` → SPY×FAMILY 第2回

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_units` | `mst_unit_id` → `id` | ボーナス対象ユニットの定義 |
| `mst_quest_event_bonus_schedules` | `event_bonus_group_id` → `event_bonus_group_id` | ボーナスが適用されるクエストスケジュール |
| `mst_stage_event_settings` | `event_bonus_group_id` → `event_bonus_group_id` | ボーナスが適用されるステージ設定 |

---

## 実データ例

### パターン1: 均一ボーナス設定（怪獣8号第1回 全キャラ30%）

```
ENABLE, id, mst_unit_id,     bonus_percentage, event_bonus_group_id, is_pick_up, release_key
e,       1,  chara_kai_00101, 30,              raid_kai_00001,       NULL,       202509010
e,       2,  chara_kai_00301, 30,              raid_kai_00001,       NULL,       202509010
e,       3,  chara_kai_00001, 30,              raid_kai_00001,       NULL,       202509010
```

第1回イベントでは作品キャラ全員に同じ30%ボーナスを設定する単純な設計。

### パターン2: 段階的ボーナス設定（魔都精兵のスレイブ）

```
ENABLE, id, mst_unit_id,     bonus_percentage, event_bonus_group_id, is_pick_up, release_key
e,      32,  chara_sur_00501, 20,              raid_sur1_00001,      NULL,       202512010
e,      33,  chara_sur_00101, 15,              raid_sur1_00001,      NULL,       202512010
e,      34,  chara_sur_00601, 10,              raid_sur1_00001,      NULL,       202512010
e,      35,  chara_sur_00701, 10,              raid_sur1_00001,      NULL,       202512010
e,      37,  chara_sur_00801, 10,              raid_sur1_00001,      NULL,       202512010
e,      38,  chara_sur_00201, 5,               raid_sur1_00001,      NULL,       202512010
e,      39,  chara_sur_00301, 5,               raid_sur1_00001,      NULL,       202512010
e,      40,  chara_sur_00001, 3,               raid_sur1_00001,      NULL,       202512010
```

最新ガチャキャラ（20%）> 高レアリティキャラ（15%）> 中堅キャラ（10%）> 旧キャラ（5〜3%）の段階設定。新ガチャキャラを使うメリットを強調する設計。

---

## 設定時のポイント

1. **同一 `event_bonus_group_id` に複数ユニットを設定することで複数キャラにボーナスを付与できる**。同一グループ内でユニットごとに異なる `bonus_percentage` を設定することも可能。

2. **`event_bonus_group_id` はクエスト側の設定と必ず一致させる**。このIDが `MstQuestEventBonusSchedule` や `MstStageEventSetting` に設定されていないとボーナスが適用されない。

3. **`bonus_percentage` の設定はガチャ誘導戦略と整合させる**。最新イベントガチャで排出されるユニットに高い倍率を設定し、既存キャラに低い倍率を設定するのが一般的なパターン。

4. **`id` は1始まりの連番整数**。CSVへの行追加時は既存最大値+1を使用する。

5. **`is_pick_up` フラグは現在の実データではNULLのまま使用されていない**。将来的な機能拡張のために定義されているカラムのため、現時点では `NULL` を設定する。

6. **`mst_unit_id` は `mst_units` テーブルに存在する有効なIDのみ設定できる**。存在しないユニットIDを設定するとデータロードエラーの原因になる。

7. **イベント期間外にもボーナスグループのデータは保持される**。過去のイベントデータとして残るため、CSVからは削除しないこと。
