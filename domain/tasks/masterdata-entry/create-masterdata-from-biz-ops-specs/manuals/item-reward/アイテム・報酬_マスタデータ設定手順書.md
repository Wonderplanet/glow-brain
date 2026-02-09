# アイテム・報酬 マスタデータ設定手順書

## 概要

アイテム・報酬のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

アイテム・報酬のマスタデータは、以下の3テーブル構成で作成します。

**アイテム基本情報**:
- **MstItem** - アイテムの基本情報(タイプ、レアリティ、効果値等)
- **MstItemI18n** - アイテム名・説明文(多言語対応)

**報酬情報**:
- **MstMissionReward** - ミッション報酬の定義(リソースタイプ、数量等)

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

運営仕様書・ヒーロー設計書・クエスト設計書から以下の情報を抽出します。

**必要情報**:
- アイテムの基本情報(アイテムID、アイテム名、説明文)
- アイテムタイプ(CharacterFragment、RankUpMaterial、Ticket等)
- グループタイプ(Etc等)
- レアリティ(UR、SSR、SR、R、N)
- 効果値(キャラID等)
- 開始・終了日時
- リリースキー
- 報酬グループID
- 報酬のリソースタイプ(Item、Coin、FreeDiamond等)
- 報酬のリソースID(アイテムIDやチケットID等)
- 報酬の数量

### 2. MstItem シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,type,group_type,rarity,asset_key,effect_value,sort_order,start_date,end_date,release_key,item_type,destination_opr_product_id
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | アイテムの一意識別子。下記の「ID採番ルール」を参照 |
| **type** | アイテムタイプ。下記の「type設定一覧」を参照 |
| **group_type** | グループタイプ。通常は `Etc` |
| **rarity** | レアリティ。下記の「rarity設定一覧」を参照 |
| **asset_key** | アセットキー。通常はMstItem.idと同じ値 |
| **effect_value** | 効果値。キャラメモリーの場合はキャラID、それ以外は空欄 |
| **sort_order** | 表示順序。基本的に `1` を使用 |
| **start_date** | 開始日時。例: `2026-01-16 15:00:00` |
| **end_date** | 終了日時。例: `2037-12-31 23:59:59` (恒久的なアイテムの場合) |
| **release_key** | リリースキー。例: `202601010` |
| **item_type** | アイテムタイプ(typeと同じ値を設定) |
| **destination_opr_product_id** | 遷移先商品ID。通常は空欄 |

#### 2.3 type設定一覧

アイテムで使用可能なtypeは以下の通りです。**大文字小文字を正確に一致**させてください。

| type | 説明 | 用途 |
|------|------|------|
| **CharacterFragment** | キャラクター欠片 | キャラクターのグレードアップに使用 |
| **RankUpMaterial** | ランクアップ素材 | キャラクターのランクアップに使用 |
| **Ticket** | チケット | ガチャ等に使用するチケット |
| **MemoryFragment** | メモリーフラグメント | 汎用的な育成素材 |
| **Memory** | メモリー | 汎用的な育成素材 |
| **Coin** | コイン | ゲーム内通貨 |

**頻繁に使用されるtype**:
- CharacterFragment(キャラ欠片)
- RankUpMaterial(キャラメモリー)
- Ticket(ガチャチケット)

#### 2.4 rarity設定一覧

| rarity | 説明 | レアリティ |
|--------|------|-----------|
| **UR** | Ultra Rare | 最高レアリティ |
| **SSR** | Super Super Rare | 非常に高レアリティ |
| **SR** | Super Rare | 高レアリティ |
| **R** | Rare | 中レアリティ |
| **N** | Normal | 通常レアリティ |

#### 2.5 ID採番ルール

アイテムのIDは、以下の形式で採番します。

**キャラクター欠片**:
```
piece_{series_id}_{連番5桁}
```

**キャラメモリー**:
```
memory_chara_{series_id}_{連番5桁}
```

**チケット・汎用アイテム**:
```
ticket_glo_{連番5桁}
memoryfragment_glo_{連番5桁}
memory_glo_{連番5桁}
```

**パラメータ**:
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
  - glo = GLOW全体
- `連番5桁`: シリーズ内で001からゼロパディング

**採番例**:
```
piece_jig_00401   (地獄楽 キャラ401の欠片)
memory_chara_jig_00601   (地獄楽 キャラ601のメモリー)
ticket_glo_00001   (GLOWチケット001)
```

#### 2.6 作成例

```
ENABLE,id,type,group_type,rarity,asset_key,effect_value,sort_order,start_date,end_date,release_key,item_type,destination_opr_product_id
e,memory_chara_jig_00601,RankUpMaterial,Etc,SR,memory_chara_jig_00601,chara_jig_00601,1013,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,RankUpMaterial,
e,memory_chara_jig_00701,RankUpMaterial,Etc,SR,memory_chara_jig_00701,chara_jig_00701,1014,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,RankUpMaterial,
e,piece_jig_00401,CharacterFragment,Etc,UR,piece_jig_00401,,1,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,CharacterFragment,
e,piece_jig_00501,CharacterFragment,Etc,SSR,piece_jig_00501,,1,"2026-01-16 15:00:00","2037-12-31 23:59:59",202601010,CharacterFragment,
```

#### 2.7 アイテム設定のポイント

- **effect_value**: キャラメモリーの場合は対応するキャラID(`chara_jig_00601`等)を設定
- **start_date / end_date**: イベント開始日と終了日を設定(恒久的なアイテムは`2037-12-31 23:59:59`を推奨)
- **sort_order**: 表示順序は基本的に `1` を使用(特別な表示順が必要な場合のみ変更)
- **item_type**: typeと同じ値を設定

### 3. MstItemI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_item_id,language,name,description
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstItemと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_item_id}_{locale}` |
| **mst_item_id** | アイテムID。MstItem.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語(簡体字)、`zh-TW`: 中国語(繁体字) |
| **name** | アイテム名 |
| **description** | アイテム説明文 |

#### 3.3 作成例

```
ENABLE,release_key,id,mst_item_id,language,name,description
e,202601010,memory_chara_jig_00601_ja,memory_chara_jig_00601,ja,"民谷 巌鉄斎のメモリー","民谷 巌鉄斎のLv.上限開放に使用するアイテム"
e,202601010,memory_chara_jig_00701_ja,memory_chara_jig_00701,ja,メイのメモリー,メイのLv.上限開放に使用するアイテム
e,202601010,piece_jig_00401_ja,piece_jig_00401,ja,"賊王 亜左 弔兵衛のかけら","賊王 亜左 弔兵衛のグレードアップに使用するアイテム"
e,202601010,piece_jig_00501_ja,piece_jig_00501,ja,"山田浅ェ門 桐馬のかけら","山田浅ェ門 桐馬のグレードアップに使用するアイテム"
```

### 4. MstMissionReward シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
```

#### 4.2 各カラムの設定ルール

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

#### 4.3 resource_type設定一覧

報酬で使用可能なresource_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| resource_type | 説明 | resource_id | 用途 |
|--------------|------|-------------|------|
| **Item** | アイテム | アイテムID | MstItemで定義されたアイテムを付与 |
| **Coin** | コイン | 空欄 | ゲーム内通貨を付与 |
| **FreeDiamond** | 無償ダイヤ | 空欄 | 無償プリズムを付与 |
| **PaidDiamond** | 有償ダイヤ | 空欄 | 有償プリズムを付与 |
| **Stamina** | スタミナ | 空欄 | スタミナを付与 |
| **Experience** | 経験値 | 空欄 | プレイヤー経験値を付与 |

**頻繁に使用されるresource_type**:
- Item(アイテム付与)
- Coin(コイン付与)
- FreeDiamond(無償ダイヤ付与)

#### 4.4 group_id命名規則

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

**採番例**:
```
event_jig_00001_daily_bonus_01   (地獄楽イベント ログボ1日目)
jig_00001_event_reward_01   (地獄楽イベント 報酬01)
jig_00001_limited_term_1   (地獄楽イベント 期間限定ミッション1)
```

#### 4.5 作成例

```
ENABLE,id,release_key,group_id,resource_type,resource_id,resource_amount,sort_order,備考
e,mission_reward_463,202601010,event_jig_00001_daily_bonus_01,Item,ticket_glo_00003,2,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_464,202601010,event_jig_00001_daily_bonus_02,Coin,,5000,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_465,202601010,event_jig_00001_daily_bonus_03,FreeDiamond,,40,1,"地獄楽 いいジャン祭 特別ログインボーナス"
e,mission_reward_480,202601010,jig_00001_event_reward_01,Item,memory_chara_jig_00701,200,1,jigいいジャン祭_ミッション
e,mission_reward_483,202601010,jig_00001_event_reward_04,Item,ticket_glo_00003,1,1,jigいいジャン祭_ミッション
```

#### 4.6 報酬設定のポイント

- **resource_type=Item**: resource_idにMstItem.idを設定
- **resource_type=Coin/FreeDiamond等**: resource_idは空欄、resource_amountに数量を設定
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
  - MstItem.id: `piece_{series_id}_{連番5桁}` または `memory_chara_{series_id}_{連番5桁}` 等
  - MstItemI18n.id: `{mst_item_id}_{locale}`
  - MstMissionReward.id: `mission_reward_{連番}`

- [ ] **リレーションの整合性**
  - `MstItemI18n.mst_item_id` が `MstItem.id` に存在する
  - `MstMissionReward.resource_id` (resource_type=Itemの場合) が `MstItem.id` に存在する

- [ ] **enum値の正確性**
  - type: CharacterFragment、RankUpMaterial、Ticket、MemoryFragment、Memory、Coin
  - rarity: UR、SSR、SR、R、N
  - resource_type: Item、Coin、FreeDiamond、PaidDiamond、Stamina、Experience
  - 大文字小文字が正確に一致している

- [ ] **日時の妥当性**
  - start_dateがイベント開始日時と一致している
  - end_dateが適切に設定されている(恒久的なアイテムは`2037-12-31 23:59:59`)

- [ ] **効果値の整合性**
  - type=RankUpMaterialの場合、effect_valueにキャラIDが設定されている
  - type=CharacterFragmentの場合、effect_valueは空欄

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語(ja)が必須で設定されている
  - 他言語(en、zh-CN、zh-TW)も設定されている

- [ ] **アイテム名・説明文の品質**
  - 誤字脱字がない
  - アイテムの用途が明確に記載されている

- [ ] **報酬バランスの妥当性**
  - 報酬数量が適切な範囲内
  - グループ内の報酬構成が適切

## 出力フォーマット

最終的な出力は以下の3シート構成で行います。

### MstItem シート

| ENABLE | id | type | group_type | rarity | asset_key | effect_value | sort_order | start_date | end_date | release_key | item_type | destination_opr_product_id |
|--------|----|----|----------|--------|----------|------------|-----------|----------|---------|------------|----------|--------------------------|
| e | memory_chara_jig_00601 | RankUpMaterial | Etc | SR | memory_chara_jig_00601 | chara_jig_00601 | 1013 | 2026-01-16 15:00:00 | 2037-12-31 23:59:59 | 202601010 | RankUpMaterial | |

### MstItemI18n シート

| ENABLE | release_key | id | mst_item_id | language | name | description |
|--------|------------|----|------------|---------|------|-----------|
| e | 202601010 | memory_chara_jig_00601_ja | memory_chara_jig_00601 | ja | 民谷 巌鉄斎のメモリー | 民谷 巌鉄斎のLv.上限開放に使用するアイテム |

### MstMissionReward シート

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order | 備考 |
|--------|----|------------|---------|--------------|------------|----------------|-----------|------|
| e | mission_reward_463 | 202601010 | event_jig_00001_daily_bonus_01 | Item | ticket_glo_00003 | 2 | 1 | 地獄楽 いいジャン祭 特別ログインボーナス |

## 重要なポイント

- **3テーブル構成**: アイテム・報酬は3テーブルで管理
- **I18nは独立したシート**: MstItemI18nは独立したシートとして作成
- **アイテムタイプの正確性**: type、group_type、rarityの設定が重要
- **効果値の適切な設定**: キャラメモリーの場合は必ずeffect_valueにキャラIDを設定
- **報酬グループIDの命名規則**: イベントやミッションで参照されるため、命名規則を遵守
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
