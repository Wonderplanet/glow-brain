# MstDungeon 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeon.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeon` は**限界チャレンジ（ダンジョン機能）の基本設定テーブル**。

限界チャレンジとは、プレイヤーがどこまで深く進めるかを競うエンドレス型のコンテンツで、フロアを突破するたびに敵が強くなっていく。各開催回の基本パラメータ（スタミナコスト・ルーレットポイント・BGM・開催期間）を管理する。

2026年3月時点でCSVファイルは未作成（`MstDungeon.csv` は存在しない）。スキーマは定義済みでサービス開始後に新規追加される予定のコンテンツ。

### ゲームプレイへの影響

- **`stamina_cost`**: 限界チャレンジ1回の挑戦に必要なスタミナ量（キャンペーン適用前の基本値）
- **ルーレットポイント**: `normal_enemy_roulette_point`・`rare_enemy_roulette_point`・`boss_enemy_roulette_point` で敵種別ごとのポイント付与量を制御する。フロア進行に伴うカード選択ルーレットのリソース
- **BGM**: 通常フロアとボスフロアで異なるBGMを設定可能
- **開催期間**: `start_at`〜`end_at` の範囲内でプレイ可能

### 関連テーブルとの構造図

```
MstDungeon（開催回の基本設定）
  └─ id → MstDungeonBlock.mst_dungeon_id（1:N、フロア構成ブロック）
  └─ id → MstDungeonDepthSetting.mst_dungeon_id（1:N、深度別パラメータ）
  └─ id → MstDungeonCardGroup.mst_dungeon_id（1:N、深度別カード候補グループ）
  └─ id → MstDungeonDepthReward.mst_dungeon_id（1:N、フロア到達報酬）
```

---

## 全カラム一覧

### mst_dungeons カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。限界チャレンジマスタID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `stamina_cost` | int unsigned | 不可 | - | スタミナコスト（キャンペーン適用前の基本値） |
| `normal_enemy_roulette_point` | int unsigned | 不可 | - | 通常敵撃破時のルーレットポイント付与量 |
| `rare_enemy_roulette_point` | int unsigned | 不可 | - | レア敵撃破時のルーレットポイント付与量 |
| `boss_enemy_roulette_point` | int unsigned | 不可 | - | ボス敵撃破時のルーレットポイント付与量 |
| `bgm_asset_key` | varchar(255) | 不可 | - | 通常フロアBGMのアセットキー |
| `boss_bgm_asset_key` | varchar(255) | 不可 | - | ボスフロアBGMのアセットキー |
| `start_at` | timestamp | 不可 | - | 開催開始日時（UTC） |
| `end_at` | timestamp | 不可 | - | 開催終了日時（UTC） |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeon_blocks` | `id` ← `mst_dungeon_id` | フロア1つひとつのブロック定義（敵種別・インゲームデータ） |
| `mst_dungeon_block_rewards` | `mst_dungeon_blocks.id` ← `mst_dungeon_block_id` | ブロッククリア報酬（`MstDungeonBlock` を経由） |
| `mst_dungeon_depth_settings` | `id` ← `mst_dungeon_id` | 深度帯ごとの敵強化係数・背景・レア出現率 |
| `mst_dungeon_card_groups` | `id` ← `mst_dungeon_id` | 深度帯ごとのカード候補グループ |
| `mst_dungeon_depth_rewards` | `id` ← `mst_dungeon_id` | フロア到達報酬（マイルストーン報酬） |

---

## 実データ例

> 2026年3月現在、`MstDungeon.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: 通常の限界チャレンジ開催回

```
ENABLE: e
id: dungeon_00001
release_key: 202601010
stamina_cost: 10
normal_enemy_roulette_point: 10
rare_enemy_roulette_point: 30
boss_enemy_roulette_point: 100
bgm_asset_key: dungeon_bgm_normal_01
boss_bgm_asset_key: dungeon_bgm_boss_01
start_at: 2026-01-16 15:00:00
end_at: 2026-02-16 14:59:59
```

### パターン2: ルーレットポイント増量キャンペーン回

```
ENABLE: e
id: dungeon_00002
release_key: 202602015
stamina_cost: 10
normal_enemy_roulette_point: 15
rare_enemy_roulette_point: 45
boss_enemy_roulette_point: 150
bgm_asset_key: dungeon_bgm_normal_01
boss_bgm_asset_key: dungeon_bgm_boss_01
start_at: 2026-02-02 15:00:00
end_at: 2026-03-02 10:59:59
```

---

## 設定時のポイント

1. **ルーレットポイントのバランスは三種の倍率で調整**。通常敵 : レア敵 : ボス敵 = 1 : 3 : 10 程度の比率が基本となる。ボス敵はフロアに1体のみ出現するため、高いポイントを設定してもゲームバランスへの影響は限定的。

2. **スタミナコストはキャンペーン割引前の基本値を設定**。実際の消費スタミナはサーバー側でキャンペーン割引を適用して計算するため、ここでは定価を設定する。

3. **BGMアセットキーは通常フロアとボスフロアを別々に設定可能**。同じキーを設定すると全フロアで同じBGMが流れる。ボスフロアはより緊張感のあるBGMを選択することを推奨。

4. **`start_at` / `end_at` はUTCで設定**。日本時間（JST = UTC+9）で設計する場合は9時間引いた値を設定すること。例: JST 2026-01-16 24:00 → UTC 2026-01-16 15:00

5. **開催期間が終了しても過去データとして保持される**。履歴参照のため、期間終了後もCSVからは削除しない。

6. **1つの `MstDungeon` IDに対して複数の `MstDungeonDepthSetting` を設定する必要がある**。深度1以上の設定がないとゲームが正常に動作しないため、`MstDungeon` 追加時は必ず深度設定も合わせて追加する。

7. **`id` の命名規則はプロジェクトで統一する**。将来のデータ追加時に既存パターンを踏襲すること。
