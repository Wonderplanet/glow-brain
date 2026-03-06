# MstDungeonBlock 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeonBlock.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeonBlock` は**限界チャレンジ（ダンジョン）の各フロア（ブロック）定義テーブル**。

限界チャレンジは複数のブロックで構成されており、各ブロックには「通常・レア・ボス」の種別があり、それぞれ異なるインゲームデータ（`MstInGame`）を参照する。プレイヤーはブロックをクリアするたびに次のブロックに進む。ランダムにレアブロックが出現する設計になっている。

2026年3月時点でCSVファイルは未作成（`MstDungeonBlock.csv` は存在しない）。

### ゲームプレイへの影響

- **`block_type`**: ブロック種別（通常・レア・ボス）によって出現する敵の強さや報酬が変わる
- **`mst_in_game_id`**: ブロック内で使用するインゲームデータ（ステージ構成・敵配置等）を参照
- 1ブロックあたり1つの `MstInGame` データが対応する

### 関連テーブルとの構造図

```
MstDungeon（開催回の基本設定）
  └─ id → MstDungeonBlock.mst_dungeon_id（1:N）
                ├─ block_type = Normal  （通常ブロック）
                ├─ block_type = Rare    （レアブロック）
                └─ block_type = Boss    （ボスブロック）
                      └─ mst_in_game_id → MstInGame.id（インゲームステージデータ）
                      └─ id → MstDungeonBlockReward.mst_dungeon_block_id（1:N、ブロッククリア報酬）
```

---

## 全カラム一覧

### mst_dungeon_blocks カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。ブロックID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_dungeon_id` | varchar(255) | 不可 | - | 参照先ダンジョンID（`mst_dungeons.id`） |
| `block_type` | enum | 不可 | - | ブロック種別。`Normal` / `Rare` / `Boss` の3種 |
| `mst_in_game_id` | varchar(255) | 不可 | - | 参照先インゲームID（`mst_in_games.id`）。1ブロック = 1インゲームデータ |

---

## DungeonBlockType（ブロック種別）

| 値 | 意味 | 特徴 |
|----|------|------|
| `Normal` | 通常ブロック | 標準的な難易度の敵が出現する。レア出現率に応じてレアブロックに置換される可能性あり |
| `Rare` | レアブロック | 通常より難しいが報酬が豪華なブロック。`MstDungeonDepthSetting.rare_block_percentage` で出現確率を制御 |
| `Boss` | ボスブロック | 一定深度ごとに出現する強力なボス敵ブロック。クリアすると特別報酬が得られる |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeons` | `mst_dungeon_id` → `id` | 属する開催回の基本設定 |
| `mst_in_games` | `mst_in_game_id` → `id` | ブロック内のステージ構成・敵配置・勝利条件等 |
| `mst_dungeon_block_rewards` | `id` ← `mst_dungeon_block_id` | ブロッククリア時の報酬定義 |
| `mst_dungeon_depth_settings` | `mst_dungeon_id` 経由 | 深度帯ごとの敵強化係数・レアブロック出現率 |

---

## 実データ例

> 2026年3月現在、`MstDungeonBlock.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: 通常ブロック

```
ENABLE: e
id: dungeon_00001_normal_01
release_key: 202601010
mst_dungeon_id: dungeon_00001
block_type: Normal
mst_in_game_id: ingame_dungeon_normal_01
```

### パターン2: ボスブロック

```
ENABLE: e
id: dungeon_00001_boss_01
release_key: 202601010
mst_dungeon_id: dungeon_00001
block_type: Boss
mst_in_game_id: ingame_dungeon_boss_01
```

---

## 設定時のポイント

1. **各ダンジョン開催回に対してブロックタイプごとに複数のブロックを用意する**。通常ブロック・レアブロック・ボスブロックがそれぞれ複数存在し、ゲーム進行に応じてランダム選択やローテーションで使用される。

2. **`mst_in_game_id` には必ず有効なインゲームIDを指定する**。インゲームデータには敵の種類・配置・勝利条件が含まれており、これがないとブロックが正常にプレイできない。

3. **ブロック種別の比率に注意**。通常ブロックを多めに用意し、レア・ボスはそれより少なくするのが一般的な設計。ボスブロックは一定深度ごとに必ず出現するよう設計する。

4. **同一 `mst_dungeon_id` 内でのブロック数を適切に設定する**。ブロック数が少なすぎると同じステージが繰り返し出現してプレイ体験が単調になる。

5. **`MstDungeon` を追加した際は、対応するブロックデータも必ず追加する**。ブロックが存在しないダンジョンは機能しない。

6. **`id` の命名は `{dungeon_id}_{block_type}_{連番}` 形式を推奨**。関連するダンジョンIDを含めることで、どのダンジョンのブロックかを一目で識別できる。
