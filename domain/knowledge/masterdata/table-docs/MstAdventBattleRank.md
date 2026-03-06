# MstAdventBattleRank 詳細説明

> CSVパス: `projects/glow-masterdata/MstAdventBattleRank.csv`

---

## 概要

降臨バトルにおけるランク（段位）の定義を管理するテーブル。各ランクは「ランクタイプ」（Bronze/Silver/Gold/Master）と「ランクレベル」（タイプ内の細かいレベル）の2次元で構成され、そのランクに到達するために必要な最低スコアが設定される。

プレイヤーが降臨バトルで獲得したスコアと照合することで、現在のランクが決定される。ランクに応じて報酬が付与される仕組みで、`mst_advent_battle_reward_groups` と連携する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。ランクレコードを一意に識別するID |
| mst_advent_battle_id | varchar(255) | YES | 紐付く降臨バトルの `mst_advent_battles.id` |
| rank_type | enum | YES | ランクタイプ（`Bronze` / `Silver` / `Gold` / `Master`） |
| rank_level | tinyint unsigned | YES | ランクタイプ内のレベル番号 |
| required_lower_score | bigint unsigned | YES | このランク・レベルに到達するために必要な最低スコア |
| asset_key | varchar(255) | YES | ランクアイコン等のアセットキー（空文字の場合あり） |
| release_key | bigint | YES | リリースキー |

---

## rank_type（ランクタイプ）

| 値 | 説明 | 位置づけ |
|----|------|---------|
| `Bronze` | ブロンズランク | 最低ランク |
| `Silver` | シルバーランク | 中間ランク |
| `Gold` | ゴールドランク | 上位ランク |
| `Master` | マスターランク | 最高ランク |

---

## 命名規則 / IDの生成ルール

- `id`: `{mst_advent_battle_id}_rank_{連番（2桁）}`（例: `quest_raid_kai_00001_rank_01`）
- 連番は `rank_type` × `rank_level` の昇順で振る

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_advent_battles` | 多対1 | 対象の降臨バトル。`mst_advent_battle_id` で参照 |
| `mst_advent_battle_reward_groups` | 1対多 | ランク達成報酬の定義。`reward_category='Rank'` で紐付く |

---

## 実データ例

### パターン1: Bronzeランクのレベル定義

```
ENABLE: e
id: quest_raid_kai_00001_rank_01
mst_advent_battle_id: quest_raid_kai_00001
rank_type: Bronze
rank_level: 1
required_lower_score: 1000
asset_key: （空文字）
release_key: 202509010

ENABLE: e
id: quest_raid_kai_00001_rank_02
mst_advent_battle_id: quest_raid_kai_00001
rank_type: Bronze
rank_level: 2
required_lower_score: 5000
asset_key: （空文字）
release_key: 202509010

ENABLE: e
id: quest_raid_kai_00001_rank_03
mst_advent_battle_id: quest_raid_kai_00001
rank_type: Bronze
rank_level: 3
required_lower_score: 10000
asset_key: （空文字）
release_key: 202509010
```

### パターン2: Silverランクへの昇格定義

```
ENABLE: e
id: quest_raid_kai_00001_rank_05
mst_advent_battle_id: quest_raid_kai_00001
rank_type: Silver
rank_level: 1
required_lower_score: 30000
asset_key: （空文字）
release_key: 202509010
```

---

## 設定時のポイント

1. **スコア閾値の単調増加**: `required_lower_score` は `rank_level` が上がるにつれて必ず大きい値を設定する。また、Bronze → Silver → Gold → Master の順でも大きくなる必要がある。
2. **rank_level はタイプ内での連番**: `rank_level` は `rank_type` ごとに 1 から始まる連番。Bronze は1〜N、Silver は1〜M のように、各タイプ独立して番号を振る。
3. **asset_key の設定**: ランクアイコン画像のアセットキーを設定する。空文字の場合はデフォルトアセットが使用される。
4. **スコアバランスの調整**: `required_lower_score` の設定値はゲームバランスに大きく影響する。既存の降臨バトルのスコア分布を参考に、適切な閾値を設定する。
5. **全ランクのセット作成**: 降臨バトルを追加する際は、Bronze から Master まで全ランクのレコードをセットで作成する。
6. **連番 id の管理**: `id` の連番は、ランクタイプとランクレベルの組み合わせ順に振る。途中でランクを追加・削除する場合は既存 id の連番を崩さないよう注意する。
