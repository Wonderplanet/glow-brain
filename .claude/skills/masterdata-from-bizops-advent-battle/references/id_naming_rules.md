# ID命名規則・採番ルール

## 概要

このドキュメントは、マスタデータCSV作成時のID命名規則と採番ルールを定義します。これらのルールに従うことで、既存データとの整合性を保ち、ID重複を防ぎます。

## ID採番の基本原則

### 1. 既存データからの最大ID取得

**重要**: 新規IDを採番する前に、必ず既存データの最大IDを確認してください。

```
手順:
1. 対象テーブルのCSVファイルを確認
2. ID列から数値部分を抽出
3. 最大値を取得
4. 最大値 + 1 から採番開始
```

**実装例(擬似コード)**:
```typescript
/**
 * 既存データから次のIDを取得
 */
function getNextId(tableName: string, idColumn: string, pastDataDir: string): number {
  const csvPath = path.join(pastDataDir, `${tableName}.csv`)
  if (!fs.existsSync(csvPath)) {
    return 1 // 過去データがない場合は1から開始
  }

  const existingData = parseCSV(csvPath)
  const maxId = Math.max(...existingData.map(row => extractNumber(row[idColumn])))
  return maxId + 1
}

/**
 * IDから数値部分を抽出
 */
function extractNumber(id: string): number {
  const match = id.match(/(\d+)$/)
  return match ? parseInt(match[1]) : 0
}
```

### 2. ID形式の統一

同じテーブル内では、ID形式を統一してください。

**NG例**:
```
mission_reward_1
mission_reward_02
mission_reward_003
```

**OK例**:
```
mission_reward_1
mission_reward_2
mission_reward_3
```

## 報酬系テーブルのID命名規則

### MstMissionReward

**形式**: `mission_reward_{連番}`

**連番ルール**:
- ゼロ埋めなし
- 既存データの最大ID + 1から開始

**例**:
```
mission_reward_463
mission_reward_464
mission_reward_465
```

### MstAdventBattleReward

**形式**: `quest_raid_{series_id}{連番}_reward_group_{連番5桁}_{連番2桁}_{末尾連番}`

**末尾連番ルール**:
- **ゼロ埋めなし** (`_1`, `_2`, `_3`, ...)
- 同一報酬グループ内で連番

**例**:
```
quest_raid_jig1_reward_group_00001_01_1
quest_raid_jig1_reward_group_00001_01_2
quest_raid_jig1_reward_group_00001_01_3
```

**重要**: `_01`, `_02`ではなく`_1`, `_2`形式

### MstStageEventReward

**形式**: `{連番}`

**連番ルール**:
- 既存データの最大ID + 1から開始
- リリースキーごとに採番範囲を管理
- 例: 202601010のデータは569～、次のリリースは600～等

**採番方法**:
```
1. マスタデータ/過去データ/{release_key}/MstStageEventReward.csv を確認
2. 最大IDを取得(例: 568)
3. 次のIDから採番開始(例: 569)
```

**例**:
```
569
570
571
```

### MstQuestFirstTimeClearReward

**形式**: `quest_first_time_clear_reward_{連番}`

**連番ルール**:
- ゼロ埋めなし
- 既存データの最大ID + 1から開始

**例**:
```
quest_first_time_clear_reward_1
quest_first_time_clear_reward_2
quest_first_time_clear_reward_3
```

## イベント系テーブルのID命名規則

### MstEvent

**形式**: `event_{series_id}_{連番5桁}`

**連番ルール**:
- ゼロ埋め5桁
- リリースキーをベースに採番(例: 202601010 → 00001)

**例**:
```
event_jig_00001
event_jig_00002
event_osh_00001
```

### MstQuest

**形式**: `quest_event_{series_id}{連番1桁}_{クエストタイプ略称}{連番2桁}`

**クエストタイプ略称**:
- `charaget`: キャラ入手クエスト
- `1day`: デイリークエスト
- `challenge`: チャレンジクエスト

**例**:
```
quest_event_jig1_charaget01
quest_event_jig1_1day
quest_event_jig1_challenge01
```

### MstStage

**形式**: `event_{series_id}{連番1桁}_{クエストタイプ略称}{連番2桁}_{連番5桁}`

**連番ルール**:
- ステージ番号はゼロ埋め5桁
- 同一クエスト内で連番

**例**:
```
event_jig1_charaget01_00001
event_jig1_charaget01_00002
event_jig1_charaget01_00003
```

## アセットキーの命名規則

### イベント基本アセットキー

**形式**: `{series_id}_{連番5桁}`

**対応関係**:
```
event_jig_00001 → jig_00001
event_osh_00002 → osh_00002
```

**例**:
```
jig_00001
jig_00002
osh_00001
```

### 背景アセットキー

**形式**: `{series_id}_{連番5桁}`

**クエストタイプ別のデフォルト値**:
- キャラ入手クエスト(`charaget01`): `jig_00003`
- デイリークエスト(`1day`): `jig_00002`
- チャレンジクエスト(`challenge01`): `jig_00001`

**例**:
```
jig_00001 (チャレンジクエスト)
jig_00002 (デイリークエスト)
jig_00003 (キャラ入手クエスト)
```

## ショップ・パック系テーブルのID命名規則

### MstStoreProduct

**形式**: `{連番}`

**連番ルール**:
- 既存データの最大ID + 1から開始
- 商品タイプに関係なく通し番号

**例**:
```
50
51
52
```

### MstPack

**形式**: `event_item_pack_{連番}` または `monthly_item_pack_{連番}`

**連番ルール**:
- イベント限定パック: `event_item_pack_{連番}`
- 月次パック: `monthly_item_pack_{連番}`
- 既存データの最大ID + 1から開始

**例**:
```
event_item_pack_12
event_item_pack_13
monthly_item_pack_5
```

### MstPackContent

**形式**: `{連番}`

**連番ルール**:
- 既存データの最大ID + 1から開始
- パック内容物ごとに連番

**例**:
```
113
114
115
```

## 降臨バトル系テーブルのID命名規則

### MstAdventBattle

**形式**: `quest_raid_{series_id}{連番1桁}_{連番5桁}`

**連番ルール**:
- イベント番号: 1桁
- バトル番号: ゼロ埋め5桁

**例**:
```
quest_raid_jig1_00001
quest_raid_jig1_00002
quest_raid_osh1_00001
```

### MstAdventBattleRewardGroup

**形式**: `quest_raid_{series_id}{連番1桁}_reward_group_{連番5桁}_{連番2桁}`

**ID変換ルール**:
```
mst_advent_battle_id: quest_raid_jig1_00001
↓ (末尾の_00001を削除)
reward_group_id: quest_raid_jig1_reward_group_00001_01
```

**例**:
```
quest_raid_jig1_reward_group_00001_01
quest_raid_jig1_reward_group_00001_02
quest_raid_jig1_reward_group_00002_01
```

## ID採番時の注意事項

### 1. 過去データの確認

新規ID採番時は、必ず以下のパスを確認してください:

```
マスタデータ/過去データ/{release_key}/{TableName}.csv
```

過去データが存在しない場合のみ、1から採番開始します。

### 2. リリースキーごとの管理

一部のテーブル(MstStageEventReward等)は、リリースキーごとにID範囲を管理しています。同じリリースキーのデータを追加する場合は、そのリリースキーの最大IDを確認してください。

### 3. ID形式の一貫性

同じテーブル内では、ID形式を統一してください。ゼロ埋めありとゼロ埋めなしを混在させないでください。

### 4. 外部キー制約

IDを採番する際は、外部キー制約を考慮してください。参照先のテーブルにIDが存在することを確認してください。

## トラブルシューティング

### Q1: 既存データの最大IDがわからない

**対処法**:
1. `マスタデータ/過去データ/{release_key}/`配下のCSVを確認
2. ID列をソートして最大値を確認
3. 最大値 + 1から採番

### Q2: ID形式が分からない

**対処法**:
1. 既存データのID形式を確認
2. このドキュメントの命名規則を参照
3. 同じテーブルの既存データと形式を合わせる

### Q3: ID重複エラーが発生した

**対処法**:
1. 既存データとの重複を確認
2. 最大ID + 1から採番し直す
3. 外部キー制約を確認

## まとめ

- **必ず既存データの最大IDを確認してから採番**
- **ID形式を統一**
- **報酬系テーブルの末尾連番はゼロ埋めなし**(`_1`, `_2`, `_3`, ...)
- **ステージID等の連番はゼロ埋め5桁**(`00001`, `00002`, ...)
- **アセットキーはイベントIDから生成**(`event_jig_00001` → `jig_00001`)
