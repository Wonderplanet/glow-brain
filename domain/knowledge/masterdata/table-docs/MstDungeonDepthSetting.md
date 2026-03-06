# MstDungeonDepthSetting 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeonDepthSetting.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeonDepthSetting` は**限界チャレンジの深度帯ごとのゲームパラメータ設定テーブル**。

深度（進行フロア数）が上がるほど敵が強くなり、報酬も豊富になる。このテーブルでは深度帯（例: 深度1〜9、10〜19…）ごとの敵強化係数・背景アセット・レアブロック出現率・各種ポイント係数を管理する。`min_depth` で下限を指定し、次のレコードの `min_depth` 未満までが適用範囲となる区間設計。

2026年3月時点でCSVファイルは未作成。

### ゲームプレイへの影響

- **敵強化係数** (`enemy_hp_coefficient`・`enemy_attack_coefficient`・`enemy_speed_coefficient`): 各深度帯での敵の強さ。深度が深くなるほど係数を大きくして敵を強化する
- **`rare_block_percentage`**: レアブロックの出現確率（%）。深度が深いほど高くすることでエキサイティングな体験を提供できる
- **`roulette_point_coefficient`**: ルーレットポイントの倍率。深度が深くなるほど多くのポイントを獲得できる
- **`series_point_coefficient`**: 作品ポイントの倍率。イベントポイントに影響する
- **`block_reward_coefficient`**: ブロッククリア報酬の倍率（作品ポイントアイテム以外の報酬に適用）

### 関連テーブルとの構造図

```
MstDungeon（開催回）
  └─ id → MstDungeonDepthSetting.mst_dungeon_id（1:N、深度帯設定）
                ├─ min_depth = 1   → 深度 1〜N の設定
                ├─ min_depth = N+1 → 深度 N+1〜M の設定
                └─ min_depth = M+1 → 深度 M+1〜 の設定（最終段階）

MstDungeonDepthSetting
  └─ block_reward_coefficient → MstDungeonBlockReward.resource_amount に乗算
  └─ roulette_point_coefficient → MstDungeon.normal/rare/boss_enemy_roulette_point に乗算
```

---

## 全カラム一覧

### mst_dungeon_depth_settings カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。深度設定ID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_dungeon_id` | varchar(255) | 不可 | - | 参照先ダンジョンID（`mst_dungeons.id`） |
| `min_depth` | int unsigned | 不可 | - | 適用深度の下限値。次のレコードの `min_depth` 未満まで適用範囲 |
| `normal_block_background_asset_key` | varchar(255) | 不可 | - | 通常ブロック背景アセットキー |
| `boss_block_background_asset_key` | varchar(255) | 不可 | - | ボスブロック背景アセットキー |
| `rare_block_percentage` | int unsigned | 不可 | - | レアブロック出現率（%）。0〜100の値 |
| `enemy_hp_coefficient` | decimal(10,4) | 不可 | - | 敵HPの係数。1.0000が基準値 |
| `enemy_attack_coefficient` | decimal(10,4) | 不可 | - | 敵攻撃力の係数。1.0000が基準値 |
| `enemy_speed_coefficient` | decimal(10,4) | 不可 | - | 敵移動速度の係数。1.0000が基準値 |
| `roulette_point_coefficient` | decimal(10,4) | 不可 | - | ルーレットポイントの係数。1.0000が基準値 |
| `series_point_coefficient` | decimal(10,4) | 不可 | - | 作品ポイントの係数。1.0000が基準値 |
| `block_reward_coefficient` | decimal(10,4) | 不可 | 1.0000 | ブロック報酬の係数。作品ポイントアイテム以外の報酬に適用 |

**ユニーク制約**: `(mst_dungeon_id, min_depth)` の組み合わせが重複不可

---

## `min_depth` の範囲区間の考え方

このテーブルは「次のレコードの `min_depth` 未満まで適用」という区間設計を採用している。

```
例: dungeon_00001 の深度設定
  レコード1: min_depth = 1   → 深度 1〜9  が適用範囲
  レコード2: min_depth = 10  → 深度 10〜24 が適用範囲
  レコード3: min_depth = 25  → 深度 25〜   が適用範囲（最終レコード、上限なし）
```

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeons` | `mst_dungeon_id` → `id` | 属する開催回の基本設定 |
| `mst_dungeon_block_rewards` | 係数として参照 | `block_reward_coefficient` でブロッククリア報酬量を補正 |
| `mst_dungeon_card_groups` | 同一 `mst_dungeon_id` | 深度帯別カード候補グループ（別テーブルで管理） |

---

## 実データ例

> 2026年3月現在、`MstDungeonDepthSetting.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: 序盤（深度1〜9）の設定

```
ENABLE: e
id: dungeon_00001_depth_setting_01
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 1
normal_block_background_asset_key: dungeon_bg_normal_stage1
boss_block_background_asset_key: dungeon_bg_boss_stage1
rare_block_percentage: 10
enemy_hp_coefficient: 1.0000
enemy_attack_coefficient: 1.0000
enemy_speed_coefficient: 1.0000
roulette_point_coefficient: 1.0000
series_point_coefficient: 1.0000
block_reward_coefficient: 1.0000
```

### パターン2: 中盤（深度10〜24）の設定

```
ENABLE: e
id: dungeon_00001_depth_setting_02
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 10
normal_block_background_asset_key: dungeon_bg_normal_stage2
boss_block_background_asset_key: dungeon_bg_boss_stage2
rare_block_percentage: 20
enemy_hp_coefficient: 2.5000
enemy_attack_coefficient: 2.0000
enemy_speed_coefficient: 1.2000
roulette_point_coefficient: 1.5000
series_point_coefficient: 1.5000
block_reward_coefficient: 1.5000
```

深度が上がると敵が2〜2.5倍強くなり、ルーレットポイントや報酬も1.5倍に増加する。

---

## 設定時のポイント

1. **各 `mst_dungeon_id` に対して `min_depth = 1` のレコードが必須**。この設定がないとゲームが起動できない。

2. **係数は `1.0000` を基準として深度が上がるほど大きくする**。`1.0000` 未満の値を設定すると敵が弱くなったり報酬が減ったりするため、通常は1.0000以上の値を設定する。

3. **背景アセットで深度帯の雰囲気を変化させることができる**。通常ブロックとボスブロックで異なる背景を設定することでビジュアル的な変化を演出できる。

4. **`rare_block_percentage` の上限は100（%）**。深度が深くなるほど高くすることでレアブロックが出やすくなり、難易度とともに興奮感が増す設計が可能。

5. **`decimal(10,4)` 型の係数は小数点4桁まで指定可能**。細かい調整が必要な場合は `1.2500` のような設定も有効。

6. **`block_reward_coefficient` のデフォルト値は `1.0000`**。このカラムは「作品ポイントアイテム以外」の報酬に適用されるため、作品ポイントアイテムとその他報酬で別々に倍率を制御できる設計になっている。

7. **ユニーク制約 `(mst_dungeon_id, min_depth)` があるため同一の組み合わせは登録不可**。深度帯を変更したい場合は既存レコードを更新するか、新しい `min_depth` で別レコードを追加する。
