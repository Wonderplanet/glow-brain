# 報酬設定 マスタデータ詳細手順書

## 概要

報酬設定のマスタデータ作成詳細手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

報酬設定のマスタデータは、以下のテーブル構成で作成します。

**主要報酬テーブル**:
- **MstMissionReward** - ミッション報酬の定義(リソースタイプ、数量等)
- **MstQuestFirstTimeClearReward** - クエスト初回クリア報酬
- **MstEventExchangeReward** - イベント交換報酬

**その他報酬テーブル**:
- MstQuestWaveReward - クエストウェーブ報酬
- MstQuestRankingReward - クエストランキング報酬
- MstLoginBonusReward - ログインボーナス報酬
- MstAdventBattleReward - 降臨バトル報酬
- MstAdventBattleWaveReward - 降臨バトルウェーブ報酬
- MstAdventBattleTotalScoreReward - 降臨バトルスコア報酬
- MstPvpSeasonReward - PVPシーズン報酬
- MstPvpRankReward - PVPランク報酬
- MstPvpTotalWinReward - PVP累計勝利数報酬
- MstPassMissionReward - パスミッション報酬
- MstShopItemReward - ショップアイテム報酬
- MstPurchaseBonus - 購入ボーナス
- その他多数

## 作成フロー

### 1. 仕様書の確認

運営仕様書から以下の情報を抽出します。

**必要情報**:
- 報酬グループID
- 報酬のリソースタイプ(Item、Coin、FreeDiamond等)
- 報酬のリソースID(アイテムIDやチケットID等)
- 報酬の数量
- リリースキー

### 2. 報酬テーブル シートの作成

#### 2.1 シートスキーマ(MstMissionReward)

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 報酬の一意識別子。命名規則: `mission_reward_{連番}` |
| **release_key** | リリースキー。例: `202601010` |
| **group_id** | 報酬グループID。ミッション等で参照されるグループ識別子 |
| **resource_type** | リソースタイプ。下記の「resource_type設定一覧」を参照 |
| **resource_id** | リソースID。Itemの場合はアイテムID、それ以外は空欄 |
| **resource_amount** | リソース数量 |
| **sort_order** | 表示順序。通常は `1` |
| **備考** | 任意の備考(CSVでは表示されるが、DB登録時は無視される場合がある) |

#### 2.3 resource_type設定一覧

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

#### 2.4 group_id命名規則

報酬グループIDは、以下の形式で採番します。

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

#### 2.5 作成例

```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_463,202601010,event_jig_00001_daily_bonus_01,Item,ticket_glo_00003,2,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_464,202601010,event_jig_00001_daily_bonus_02,Coin,,5000,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_465,202601010,event_jig_00001_daily_bonus_03,FreeDiamond,,40,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_480,202601010,jig_00001_event_reward_01,Item,memory_chara_jig_00701,200,1,jigいいジャン祭_ミッション
e,mission_reward_483,202601010,jig_00001_event_reward_04,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
```

#### 2.6 報酬設定のポイント

- **resource_type=Item**: resource_idにMstItem.idを設定
- **resource_type=Unit**: resource_idにMstUnit.idを設定
- **resource_type=Emblem**: resource_idにMstEmblem.idを設定
- **resource_type=Artwork**: resource_idにMstArtwork.idを設定
- **resource_type=Coin/FreeDiamond/PaidDiamond/Stamina/Experience**: resource_idは空欄、resource_amountに数量を設定
- **group_id**: 同じグループに複数の報酬を設定可能(sort_orderで表示順を制御)
- **備考**: 報酬の用途を記載すると管理しやすい

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstMissionReward.id: `mission_reward_{連番}`
  - 他の報酬テーブル.id: `{テーブル名のスネークケース}_{連番}`

- [ ] **リレーションの整合性**
  - `報酬テーブル.resource_id` (resource_type=Itemの場合) が `MstItem.id` に存在する
  - `報酬テーブル.resource_id` (resource_type=Unitの場合) が `MstUnit.id` に存在する
  - `報酬テーブル.resource_id` (resource_type=Emblemの場合) が `MstEmblem.id` に存在する
  - `報酬テーブル.resource_id` (resource_type=Artworkの場合) が `MstArtwork.id` に存在する

- [ ] **enum値の正確性**
  - resource_type: Item、Unit、Emblem、Artwork、Coin、FreeDiamond、PaidDiamond、Stamina、Experience
  - 大文字小文字が正確に一致している

- [ ] **resource_idの妥当性**
  - resource_type=Item/Unit/Emblem/Artworkの場合、resource_idが設定されている
  - resource_type=Coin/FreeDiamond/PaidDiamond/Stamina/Experienceの場合、resource_idが空欄である

### 推奨チェック項目

- [ ] **命名規則の統一**
  - group_idのプレフィックスがシリーズIDと一致している

- [ ] **報酬バランスの妥当性**
  - 報酬数量が適切な範囲内
  - グループ内の報酬構成が適切

- [ ] **備考フィールドの記載**
  - 報酬の用途が明確に記載されている

## 出力フォーマット

最終的な出力は以下の形式で行います。

### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|------------|---------|--------------|------------|----------------|-----------|------|
| e | mission_reward_463 | 202601010 | event_jig_00001_daily_bonus_01 | Item | ticket_glo_00003 | 2 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス |

## 重要なポイント

- **汎用的な報酬設定**: 17個の報酬テーブルに共通のルールを適用
- **resource_type別の参照先**: 各resource_typeに応じた参照先テーブルを正確に設定
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
- **各機能スキルとの併用**: 各機能スキルと組み合わせて、機能全体のマスタデータを作成
