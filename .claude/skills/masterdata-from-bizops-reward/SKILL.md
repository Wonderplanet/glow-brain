---
name: masterdata-from-bizops-reward
description: 報酬設定の運営仕様書からマスタデータCSVを作成する汎用スキル。全17個の報酬テーブル(MstMissionReward等)に対応。resource_type別の参照先を明記し、各機能スキルとの併用が可能です。
---

# 報酬設定 マスタデータ作成スキル

## 概要

報酬設定の運営仕様書からマスタデータCSVを作成します。このスキルは全17個の報酬テーブルに対応した**汎用スキル**で、ミッション報酬、クエスト報酬、イベント報酬等、あらゆる報酬設定に使用できます。設計書に記載された情報を元に、DB投入可能な形式のマスタデータを自動生成し、推測で決定した値は必ずレポートします。

### 作成対象テーブル

以下の報酬テーブルを自動生成:

**主要報酬テーブル(3個)**:
- **MstMissionReward** - ミッション報酬の定義
- **MstQuestFirstTimeClearReward** - クエスト初回クリア報酬
- **MstEventExchangeReward** - イベント交換報酬

**その他報酬テーブル(14個)**:
- MstQuestWaveReward - クエストウェーブ報酬
- MstQuestRankingReward - クエストランキング報酬
- MstLoginBonusReward - ログインボーナス報酬
- MstAdventBattleReward - 降臨バトル報酬
- MstAdventBattleWaveReward - 降臨バトルウェーブ報酬
- MstAdventBattleTotalScoreReward - 降臨バトルスコア報酬
- MstPvpSeasonReward - PVPシーズン報酬
- MstPvpRankReward - PVPランク報酬
- MstPvpTotalWinReward - PVP累計勝利数報酬
- MstPassMission報酬 - パスミッション報酬
- MstShopItemReward - ショップアイテム報酬
- MstPurchaseBonus - 購入ボーナス
- その他多数

**重要**: 各報酬テーブルは独立したシートとして作成します。

## 基本的な使い方

### 必須パラメータ

以下のパラメータを指定してください:

| パラメータ名 | 説明 | 例 |
|------------|------|-----|
| **release_key** | リリースキー | `202601010` |
| **reward_table** | 作成する報酬テーブル名 | `MstMissionReward` |
| **reward_group_id** | 報酬グループID | `event_jig_00001_daily_bonus_01` |
| **rewards** | 報酬内容(JSON形式) | `[{"resource_type": "Item", "resource_id": "ticket_glo_00003", "resource_amount": 2}]` |

### 実行方法

運営仕様書ファイルを添付して、以下のプロンプトを実行してください:

```
報酬設定の運営仕様書からマスタデータを作成してください。

添付ファイル:
- イベント設計書_地獄楽_いいジャン祭.xlsx

パラメータ:
- release_key: 202601010
- reward_table: MstMissionReward
- reward_group_id: event_jig_00001_daily_bonus_01
- rewards: [
    {"resource_type": "Item", "resource_id": "ticket_glo_00003", "resource_amount": 2},
    {"resource_type": "Coin", "resource_amount": 5000},
    {"resource_type": "FreeDiamond", "resource_amount": 40}
  ]
```

## ワークフロー

### Step 1: 仕様書の読み込み

運営仕様書から以下の情報を抽出します:

**必須情報**:
- 報酬テーブル名(MstMissionReward等)
- 報酬グループID(ミッション等で参照されるグループ識別子)
- 報酬のリソースタイプ(Item、Coin、FreeDiamond等)
- 報酬のリソースID(アイテムIDやチケットID等)
- 報酬の数量

**任意情報**:
- 表示順序(記載がない場合は`1`)
- 備考(記載がない場合は空欄)

### Step 2: マスタデータ生成

詳細ルールは [references/manual.md](references/manual.md) を参照し、指定された報酬テーブルを作成します:

1. **報酬テーブル作成** - 指定されたテーブル(MstMissionReward等)を生成

#### ID採番ルール

**重要**: 新規IDを採番する前に、必ず既存データの最大IDを確認してください。

**既存データからの最大ID取得**:
```
1. マスタデータ/過去データ/{release_key}/{TableName}.csv を確認
2. ID列から数値部分を抽出
3. 最大値を取得
4. 最大値 + 1 から採番開始
```

報酬のIDは以下の形式で採番します:

**MstMissionReward**:
```
mission_reward_{連番}
```
- 連番: ゼロ埋めなし
- 既存データの最大ID + 1から開始

**MstQuestFirstTimeClearReward**:
```
quest_first_time_clear_reward_{連番}
```
- 連番: ゼロ埋めなし
- 既存データの最大ID + 1から開始

**MstAdventBattleReward**:
```
quest_raid_{series_id}{連番}_reward_group_{連番5桁}_{連番2桁}_{末尾連番}
```
- **末尾連番: ゼロ埋めなし** (`_1`, `_2`, `_3`, ...)
- 例: `quest_raid_jig1_reward_group_00001_01_1`

**MstStageEventReward**:
```
{連番}
```
- 既存データの最大ID + 1から開始
- リリースキーごとにID範囲を管理
- 例: 202601010のデータは569～

**その他の報酬テーブル**:
```
{テーブル名のスネークケース}_{連番}
```

**例**:
```
mission_reward_463 (MstMissionReward - 既存最大462の次)
quest_first_time_clear_reward_1 (MstQuestFirstTimeClearReward)
quest_raid_jig1_reward_group_00001_01_1 (MstAdventBattleReward - 末尾はゼロ埋めなし)
569 (MstStageEventReward - 既存最大568の次)
```

詳細は [references/id_naming_rules.md](references/id_naming_rules.md) を参照してください。

#### 報酬グループID命名規則

報酬グループIDは、報酬の用途に応じて以下の形式で採番します:

**イベントログインボーナス**:
```
{event_id}_daily_bonus_{連番2桁}
```

**イベント報酬**:
```
{series_id}_{event_連番5桁}_event_reward_{連番2桁}
```

**期間限定ミッション**:
```
{series_id}_{event_連番5桁}_limited_term_{連番}
```

**クエスト初回クリア報酬**:
```
{quest_id}_first_clear
```

**採番例**:
```
event_jig_00001_daily_bonus_01   (地獄楽イベント ログボ1日目)
jig_00001_event_reward_01   (地獄楽イベント 報酬01)
jig_00001_limited_term_1   (地獄楽イベント 期間限定ミッション1)
quest_jig_00001_first_clear   (地獄楽クエスト1 初回クリア報酬)
```

### Step 3: データ整合性チェック

以下の項目を自動確認し、問題があれば修正します:

- [ ] ヘッダーの列順が正しいか
- [ ] すべてのIDが一意であるか
- [ ] ID採番ルールに従っているか
- [ ] リレーションが正しく設定されているか
- [ ] enum値が正確に一致しているか(resource_type等)
- [ ] resource_type=Itemの場合、resource_idがMstItem.idに存在するか
- [ ] resource_type=Unit/Emblem/Artworkの場合、resource_idが対応するテーブルに存在するか
- [ ] resource_type=Coin/FreeDiamond/PaidDiamond等の場合、resource_idが空欄であるか

### Step 4: 推測値レポート

設計書に記載がなく、推測で決定した値を必ずレポートします。

**推測値の例**:
- `報酬テーブル.id`: 連番採番の開始番号を推測
- `報酬テーブル.sort_order`: 設計書に記載なく推測
- `報酬テーブル.group_id`: グループID命名規則を推測

### Step 5: 出力

以下の形式で出力します:

#### 1. マスタデータ(Markdown表形式)

- スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式
- 指定された報酬テーブルを作成

#### 2. 推測値レポート(必須)

作成したデータのうち、以下に該当するものを必ずレポートします:

- **添付ファイルにも手順書にも記載がなく、推測で決定したID値やパラメータ値**
- 手順書通りに作成したID値は対象外

**レポート形式:**
```
## 推測値レポート

### {テーブル名}.{カラム名}
- 値: {設定した値}
- 理由: {推測した根拠}
- 確認事項: {ユーザーが確認すべき内容}
```

**重要**: このレポートを怠ると、データインポートエラーや本番不具合のリスクが高まります。推測で決定した値は必ず報告してください。

## 出力例

### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|------------|---------|--------------|------------|----------------|-----------|------|
| e | mission_reward_463 | 202601010 | event_jig_00001_daily_bonus_01 | Item | ticket_glo_00003 | 2 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス |
| e | mission_reward_464 | 202601010 | event_jig_00001_daily_bonus_02 | Coin | | 5000 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス |
| e | mission_reward_465 | 202601010 | event_jig_00001_daily_bonus_03 | FreeDiamond | | 40 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス |

### 推測値レポート

#### MstMissionReward.id
- **値**: mission_reward_463
- **理由**: 設計書に連番の開始番号の記載がなかったため、既存の最大IDから連番を推測
- **確認事項**: 既存のMstMissionRewardで使用されているIDと重複していないことを確認してください

#### MstMissionReward.sort_order
- **値**: 1
- **理由**: 設計書に表示順序の記載がなかったため、デフォルト値`1`を設定
- **確認事項**: 同一グループ内で複数の報酬がある場合、表示順序を調整してください

## 注意事項

### resource_type設定について

報酬で使用可能なresource_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| resource_type | 説明 | resource_id | 参照先テーブル | 用途 |
|--------------|------|-------------|-------------|------|
| **Item** | アイテム | アイテムID | MstItem.id | MstItemで定義されたアイテムを付与 |
| **Unit** | ユニット(キャラクター) | ユニットID | MstUnit.id | キャラクターを付与 |
| **Emblem** | エンブレム | エンブレムID | MstEmblem.id | エンブレムを付与 |
| **Artwork** | 原画 | 原画ID | MstArtwork.id | 原画を付与 |
| **Coin** | コイン | 空欄 | - | ゲーム内通貨を付与 |
| **FreeDiamond** | 無償ダイヤ | 空欄 | - | 無償プリズムを付与 |
| **PaidDiamond** | 有償ダイヤ | 空欄 | - | 有償プリズムを付与 |
| **Stamina** | スタミナ | 空欄 | - | スタミナを付与 |
| **Experience** | 経験値 | 空欄 | - | プレイヤー経験値を付与 |

**頻繁に使用されるresource_type**:
- Item(アイテム付与)
- Coin(コイン付与)
- FreeDiamond(無償ダイヤ付与)
- Unit(キャラクター付与)

**重要な注意点**:
- `resource_type=Item`: resource_idに`MstItem.id`を設定
- `resource_type=Unit`: resource_idに`MstUnit.id`を設定
- `resource_type=Emblem`: resource_idに`MstEmblem.id`を設定
- `resource_type=Artwork`: resource_idに`MstArtwork.id`を設定
- `resource_type=Coin/FreeDiamond/PaidDiamond/Stamina/Experience`: resource_idは空欄

### 報酬グループIDについて

報酬グループIDは、以下の命名規則に従ってください:

**イベントログインボーナス**:
```
{event_id}_daily_bonus_{連番2桁}
```

**イベント報酬**:
```
{series_id}_{event_連番5桁}_event_reward_{連番2桁}
```

**期間限定ミッション**:
```
{series_id}_{event_連番5桁}_limited_term_{連番}
```

**クエスト初回クリア報酬**:
```
{quest_id}_first_clear
```

例:
- event_jig_00001_daily_bonus_01 (地獄楽イベント ログボ1日目)
- jig_00001_event_reward_01 (地獄楽イベント 報酬01)
- jig_00001_limited_term_1 (地獄楽イベント 期間限定ミッション1)
- quest_jig_00001_first_clear (地獄楽クエスト1 初回クリア報酬)

### 報酬の構成について

同じグループに複数の報酬を設定可能です。sort_orderで表示順を制御します。

**例(ログインボーナス1日目)**:
```
group_id: event_jig_00001_daily_bonus_01
- Item: ticket_glo_00003 × 2 (sort_order: 1)
- Coin: 5000 (sort_order: 2)
- FreeDiamond: 40 (sort_order: 3)
```

### 外部キー整合性について

以下のリレーションが正しく設定されていることを必ず確認してください:
- `報酬テーブル.resource_id (resource_type=Item)` → `MstItem.id`
- `報酬テーブル.resource_id (resource_type=Unit)` → `MstUnit.id`
- `報酬テーブル.resource_id (resource_type=Emblem)` → `MstEmblem.id`
- `報酬テーブル.resource_id (resource_type=Artwork)` → `MstArtwork.id`

## 各機能スキルとの併用方法

このスキルは汎用的な報酬設定用スキルであり、各機能スキルと併用することで、機能全体のマスタデータを効率的に作成できます。

### 併用例1: ガチャスキルとの併用

1. **masterdata-from-bizops-gacha** スキルでガチャ基本設定を作成
2. **masterdata-from-bizops-reward** スキルでガチャコスト設定を作成(OprGachaUseResource)

### 併用例2: ミッションスキルとの併用

1. **masterdata-from-bizops-mission** スキルでミッション基本設定を作成
2. **masterdata-from-bizops-reward** スキルでミッション報酬を作成(MstMissionReward)

### 併用例3: クエストスキルとの併用

1. **masterdata-from-bizops-quest-stage** スキルでクエスト・ステージ設定を作成
2. **masterdata-from-bizops-reward** スキルでクエスト報酬を作成(MstQuestFirstTimeClearReward)

## リファレンス

詳細なルールとenum値一覧:

- **[詳細手順書](references/manual.md)** - テーブル定義、カラム設定ルール、ID採番ルール、enum値一覧
- **[サンプル出力](examples/sample-output.md)** - 実際の出力例

## トラブルシューティング

### Q1: resource_idの設定が分からない

**対処法**:
1. `resource_type=Item/Unit/Emblem/Artwork`の場合: resource_idに対応するテーブルのIDを設定
2. `resource_type=Coin/FreeDiamond/PaidDiamond/Stamina/Experience`の場合: resource_idは空欄

### Q2: enum値のエラーが発生する

**エラー**:
```
Invalid resource_type: item (expected: Item)
```

**対処法**:
1. enum値は**大文字小文字を正確に一致**させる
2. 正しいenum値一覧は[references/manual.md](references/manual.md)を参照
3. 頻出エラー: `item` → `Item`, `coin` → `Coin`

### Q3: グループIDの命名規則が分からない

**対処法**:
- イベントログインボーナス: `{event_id}_daily_bonus_{連番2桁}`
- イベント報酬: `{series_id}_{event_連番5桁}_event_reward_{連番2桁}`
- 期間限定ミッション: `{series_id}_{event_連番5桁}_limited_term_{連番}`
- クエスト初回クリア報酬: `{quest_id}_first_clear`

## 検証

作成したマスタデータCSVは、`masterdata-csv-validator` スキルで検証できます:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv {作成したCSVファイルパス}
```

詳細は [masterdata-csv-validator](../../masterdata-csv-validator/SKILL.md) を参照してください。
