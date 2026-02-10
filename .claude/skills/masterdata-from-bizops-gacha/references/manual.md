# ガチャ マスタデータ設定手順書

## 概要

ガチャ(プレミアムガシャ、ピックアップガシャ等)のマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

ガチャのマスタデータは、以下の6テーブル構成で作成します。

**ガチャ基本情報**:
- **OprGacha** - ガチャの基本設定(タイプ、天井、期間、10連設定等)
- **OprGachaI18n** - ガチャ名・説明文(多言語対応)

**ガチャ排出内容**:
- **OprGachaPrize** - ガチャ排出内容(排出キャラ、重み、ピックアップ設定)
- **OprGachaUpper** - ガチャ天井設定(回数、タイプ)

**ガチャコスト**:
- **OprGachaUseResource** - ガチャ実行コスト(ダイヤ、チケット等)

**ガチャ表示**:
- **OprGachaDisplayUnitI18n** - ガチャ画面表示キャラの説明文(多言語対応)

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

ガチャ設計書から以下の情報を抽出します。

**必要情報**:
- ガチャID(例: Pickup_jig_001)
- ガチャタイプ(Pickup、Premium、Festival、Free等)
- 開催期間(start_at、end_at)
- ガチャ名・説明文
- 排出キャラクター一覧(ピックアップキャラ、レアリティ別排出率)
- 天井設定(上限グループ、回数)
- コスト設定(単発、10連、チケット)
- 10連設定(確定枠数)
- 表示設定(バナー、ロゴ、背景色、表示サイズ)

### 2. OprGacha シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,gacha_type,upper_group,enable_ad_play,enable_add_ad_play_upper,ad_play_interval_time,multi_draw_count,multi_fixed_prize_count,daily_play_limit_count,total_play_limit_count,daily_ad_limit_count,total_ad_limit_count,prize_group_id,fixed_prize_group_id,appearance_condition,unlock_condition_type,unlock_duration_hours,start_at,end_at,display_information_id,dev-qa_display_information_id,display_gacha_caution_id,gacha_priority,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | ガチャの一意識別子。命名規則: `Pickup_{series_id}_{連番3桁}` |
| **gacha_type** | ガチャタイプ。下記の「gacha_type設定一覧」を参照 |
| **upper_group** | 天井設定区分。通常はOprGacha.idと同じ値 |
| **enable_ad_play** | 広告で回せるか。通常は空欄 |
| **enable_add_ad_play_upper** | 広告で天井を動かすか。通常は空欄 |
| **ad_play_interval_time** | 広告で回すインターバル時間(分)。広告対応の場合のみ設定。通常は`__NULL__` |
| **multi_draw_count** | N連の指定。10連の場合は `10` |
| **multi_fixed_prize_count** | N連の確定枠数。SR以上1体確定の場合は `1` |
| **daily_play_limit_count** | 1日あたりの実行可能回数。制限なしの場合は`__NULL__` |
| **total_play_limit_count** | 期間合計の実行可能回数。制限なしの場合は`__NULL__` |
| **daily_ad_limit_count** | 1日あたりの広告実行可能回数。広告未対応の場合は `0` |
| **total_ad_limit_count** | 期間合計の広告実行可能回数。制限なしの場合は`__NULL__` |
| **prize_group_id** | 排出内容グループID。OprGacha.idと同じ値 |
| **fixed_prize_group_id** | 確定枠排出内容グループID。命名規則: `fixd_{OprGacha.id}` |
| **appearance_condition** | 表示条件。常時表示の場合は `Always` |
| **unlock_condition_type** | 解放条件タイプ。通常は `None` |
| **unlock_duration_hours** | 解放条件の期間(時間)。unlock_condition_type=Noneの場合は`__NULL__` |
| **start_at** | 開催開始日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **end_at** | 開催終了日時。形式: `YYYY-MM-DD HH:MM:SS` |
| **display_information_id** | 表示情報ID。UUID形式(Strapi管理) |
| **dev-qa_display_information_id** | 開発・QA用表示情報ID。通常はdisplay_information_idと同じ値 |
| **display_gacha_caution_id** | ガチャ注意書きID。UUID形式(Strapi管理) |
| **gacha_priority** | ガチャ表示優先度。数値が大きいほど上位に表示(例: 66、65) |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 gacha_type設定一覧

ガチャで使用可能なgacha_typeは以下の通りです。**大文字小文字を正確に一致**させてください。

| gacha_type | 説明 | 用途 |
|----------|------|------|
| **Normal** | 通常ガチャ | 常設の通常ガチャ |
| **Premium** | プレミアムガチャ | 高レアリティ確率UPガチャ |
| **Pickup** | ピックアップガチャ | 特定キャラの排出率UPガチャ |
| **Free** | 無料ガチャ | 無料で引けるガチャ |
| **Ticket** | チケットガチャ | チケット専用ガチャ |
| **Festival** | フェスティバルガチャ | 限定フェス期間のガチャ |
| **PaidOnly** | 有償限定ガチャ | 有償ダイヤ専用ガチャ |
| **Medal** | メダルガチャ | メダル交換ガチャ |
| **Tutorial** | チュートリアルガチャ | 初回限定チュートリアルガチャ |
| **StepUp** | ステップアップガチャ | 回数に応じて確率・確定枠が変化 |

**頻繁に使用されるgacha_type**:
- Pickup(ピックアップガチャ: 最も使用頻度が高い)
- Premium(プレミアムガチャ)
- Free(無料ガチャ)

#### 2.4 ID採番ルール

ガチャのIDは、以下の形式で採番します。

```
Pickup_{series_id}_{連番3桁}
Premium_{series_id}_{連番3桁}
Free_{series_id}_{連番3桁}
```

**パラメータ**:
- `gacha_type`: ガチャタイプ(Pickup、Premium、Free等)
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
  - glo = GLOW全体
- `連番3桁`: シリーズ内で001からゼロパディング

**採番例**:
```
Pickup_jig_001   (地獄楽 ピックアップガチャ1)
Pickup_jig_002   (地獄楽 ピックアップガチャ2)
Premium_osh_001  (推しの子 プレミアムガチャ1)
Free_glo_001     (GLOW全体 無料ガチャ1)
```

#### 2.5 開催期間の設定

開催期間は、以下の形式で設定します。

```
start_at: 2026-01-16 12:00:00
end_at:   2026-02-16 10:59:59
```

**注意点**:
- イベント開始時刻より早く開始することが多い(例: イベント15:00開始、ガチャ12:00開始)
- 終了時刻は、イベント終了時刻と同じかそれより前

#### 2.6 作成例

```
ENABLE,id,gacha_type,upper_group,enable_ad_play,enable_add_ad_play_upper,ad_play_interval_time,multi_draw_count,multi_fixed_prize_count,daily_play_limit_count,total_play_limit_count,daily_ad_limit_count,total_ad_limit_count,prize_group_id,fixed_prize_group_id,appearance_condition,unlock_condition_type,unlock_duration_hours,start_at,end_at,display_information_id,dev-qa_display_information_id,display_gacha_caution_id,gacha_priority,release_key
e,Pickup_jig_001,Pickup,Pickup_jig_001,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_jig_001,fixd_Pickup_jig_001,Always,None,__NULL__,"2026-01-16 12:00:00","2026-02-16 10:59:59",84b93bca-1b92-42df-9d6e-3a593fa76a69,84b93bca-1b92-42df-9d6e-3a593fa76a69,16d9cd62-8b4a-44c5-922a-6a6b7889ce06,66,202601010
e,Pickup_jig_002,Pickup,Pickup_jig_002,,,__NULL__,10,1,__NULL__,__NULL__,0,__NULL__,Pickup_jig_002,fixd_Pickup_jig_002,Always,None,__NULL__,"2026-01-16 12:00:00","2026-02-16 10:59:59",1c1d7df8-a984-4043-a38d-4463932ba6f7,1c1d7df8-a984-4043-a38d-4463932ba6f7,37543db3-0f5c-4128-993e-883a723f0232,65,202601010
```

#### 2.7 display_information_id / display_gacha_caution_idの設定

これらのIDは、Strapiで管理されているコンテンツのUUIDです。

**設定方法**:
1. Strapiで該当のガチャ情報を作成
2. 発行されたUUIDをコピー
3. display_information_idに設定
4. dev-qa_display_information_idに同じ値を設定

**注意**: Strapi連携が完了していない場合は、仮のUUIDを設定しておき、後で差し替えます。

### 3. OprGachaI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,opr_gacha_id,language,name,description,max_rarity_upper_description,pickup_upper_description,fixed_prize_description,banner_url,logo_asset_key,logo_banner_url,gacha_background_color,gacha_banner_size
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。OprGachaと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{opr_gacha_id}_{language}` |
| **opr_gacha_id** | ガチャID。OprGacha.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語(簡体字)、`zh-TW`: 中国語(繁体字) |
| **name** | ガチャ名 |
| **description** | ガチャ説明文。ピックアップキャラ名や排出率UP情報を記載 |
| **max_rarity_upper_description** | 最高レアリティ天井説明。通常は空欄 |
| **pickup_upper_description** | ピックアップ天井説明。例: `ピックアップURキャラ1体確定!` |
| **fixed_prize_description** | 確定枠説明。例: `SR以上1体確定` |
| **banner_url** | バナー画像URL。アセットキーを使用する場合は空欄 |
| **logo_asset_key** | ロゴアセットキー。命名規則: `{series_id}_{連番5桁}` |
| **logo_banner_url** | ロゴバナー画像URL。アセットキーを使用する場合は空欄 |
| **gacha_background_color** | ガチャ背景色。`Red`, `Blue`, `Green`, `Yellow`, `Colorless` 等 |
| **gacha_banner_size** | ガチャバナーサイズ。`SizeS`, `SizeM`, `SizeL` |

#### 3.3 作成例

```
ENABLE,release_key,id,opr_gacha_id,language,name,description,max_rarity_upper_description,pickup_upper_description,fixed_prize_description,banner_url,logo_asset_key,logo_banner_url,gacha_background_color,gacha_banner_size
e,202601010,Pickup_jig_001_ja,Pickup_jig_001,ja,"地獄楽 いいジャン祭ピックアップガシャ A","「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,jig_00001,pickup_a_00001,,Yellow,SizeL
e,202601010,Pickup_jig_002_ja,Pickup_jig_002,ja,"地獄楽 いいジャン祭ピックアップガシャ B","「がらんの画眉丸」と\n「山田浅ェ門 桐馬」の出現率UP中!",,ピックアップURキャラ1体確定!,SR以上1体確定,jig_00002,pickup_b_00001,,Yellow,SizeL
```

#### 3.4 descriptionの改行

説明文に改行を含める場合は、`\n`を使用します。

```
"「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中!"
```

### 4. OprGachaPrize シートの作成

#### 4.1 シートスキーマ

```
ENABLE,id,group_id,resource_type,resource_id,resource_amount,weight,pickup,release_key
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 排出内容の一意識別子。命名規則: `{group_id}_{連番}` |
| **group_id** | 排出グループID。OprGacha.prize_group_id または OprGacha.fixed_prize_group_id と対応 |
| **resource_type** | リソースタイプ。`Unit`: キャラクター、`Item`: アイテム |
| **resource_id** | リソースID。キャラの場合は `chara_{series_id}_{連番5桁}` |
| **resource_amount** | 排出数量。キャラの場合は通常 `1` |
| **weight** | 排出重み。この値が大きいほど排出率が高い |
| **pickup** | ピックアップフラグ。`1`: ピックアップ対象、`0`: 通常排出 |
| **release_key** | リリースキー。OprGachaと同じ値 |

#### 4.3 排出重みの計算

排出重みは、レアリティと排出率から計算します。

**基本的な排出率**:
- UR: 3%
- SSR: 8%
- SR: 15%
- R: 27%
- N: 47%

**ピックアップ時の排出率UP**:
- ピックアップURキャラ: UR全体の3%のうち約2%(通常URキャラは約1%に分散)
- ピックアップSSRキャラ: SSR全体の8%のうち約5%(通常SSRキャラは約3%に分散)

**重み計算例**(合計1,000,000を基準):
```
# 通常排出(ピックアップなし)
UR全体: 30,000 (3%)
  ├─ URキャラA: 1,755
  ├─ URキャラB: 1,755
  └─ ...(13体に均等分散)

SSR全体: 80,000 (8%)
  ├─ SSRキャラA: 8,840
  ├─ SSRキャラB: 8,840
  └─ ...(9体に均等分散)

SR全体: 150,000 (15%)
  ├─ SRキャラA: 32,760
  └─ ...(10体に均等分散)

R全体: 270,000 (27%)
N全体: 470,000 (47%)

# ピックアップ時
ピックアップURキャラ: 7,020(約2%)
通常URキャラ: 1,755(残りの1%を均等分散)

ピックアップSSRキャラ: 14,040(約5%)
通常SSRキャラ: 8,840(残りの3%を均等分散)
```

#### 4.4 group_idの種類

OprGachaPrizeは、以下の2種類のgroup_idで管理します。

**通常排出(prize_group_id)**:
- 単発・10連の通常排出枠
- group_id: `{opr_gacha_id}`(例: `Pickup_jig_001`)

**確定枠排出(fixed_prize_group_id)**:
- 10連の確定枠専用排出
- group_id: `fixd_{opr_gacha_id}`(例: `fixd_Pickup_jig_001`)
- 通常排出よりも高レアリティの重みが大きい

#### 4.5 作成例

**通常排出(prize_group_id: Pickup_jig_001)**:
```
ENABLE,id,group_id,resource_type,resource_id,resource_amount,weight,pickup,release_key
e,Pickup_jig_001_1,Pickup_jig_001,Unit,chara_jig_00401,1,7020,1,202601010
e,Pickup_jig_001_2,Pickup_jig_001,Unit,chara_spy_00101,1,1755,0,202601010
e,Pickup_jig_001_3,Pickup_jig_001,Unit,chara_spy_00201,1,1755,0,202601010
```

**確定枠排出(fixed_prize_group_id: fixd_Pickup_jig_001)**:
```
ENABLE,id,group_id,resource_type,resource_id,resource_amount,weight,pickup,release_key
e,fixd_Pickup_jig_001_1,fixd_Pickup_jig_001,Unit,chara_jig_00401,1,175500,1,202601010
e,fixd_Pickup_jig_001_2,fixd_Pickup_jig_001,Unit,chara_spy_00101,1,43875,0,202601010
e,fixd_Pickup_jig_001_3,fixd_Pickup_jig_001,Unit,chara_spy_00201,1,43875,0,202601010
```

**注意**: 確定枠排出は、通常排出の重みに対して約25倍の値を設定します(R/N排出を除外するため)。

### 5. OprGachaUpper シートの作成

#### 5.1 シートスキーマ

```
ENABLE,id,upper_group,upper_type,count,release_key
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 天井設定の一意識別子。連番を使用 |
| **upper_group** | 天井グループ。OprGacha.upper_groupと対応 |
| **upper_type** | 天井タイプ。`Pickup`: ピックアップ天井、`MaxRarity`: 最高レアリティ天井 |
| **count** | 天井回数。例: `100`(100回でピックアップURキャラ確定) |
| **release_key** | リリースキー。OprGachaと同じ値 |

#### 5.3 upper_type設定一覧

| upper_type | 説明 | 用途 |
|----------|------|------|
| **Pickup** | ピックアップ天井 | ピックアップキャラ確定 |
| **MaxRarity** | 最高レアリティ天井 | URキャラ確定(ピックアップ以外も含む) |

#### 5.4 作成例

```
ENABLE,id,upper_group,upper_type,count,release_key
e,17,Pickup_jig_001,Pickup,100,202601010
e,18,Pickup_jig_002,Pickup,100,202601010
```

**注意**: idは既存のテーブルと重複しないように連番で採番します。

### 6. OprGachaUseResource シートの作成

#### 6.1 シートスキーマ

```
ENABLE,id,opr_gacha_id,cost_type,cost_id,cost_num,draw_count,cost_priority,release_key
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | コスト設定の一意識別子。連番を使用 |
| **opr_gacha_id** | ガチャID。OprGacha.idと対応 |
| **cost_type** | コストタイプ。`Diamond`: ダイヤ、`Item`: アイテム(チケット) |
| **cost_id** | コストID。cost_type=Itemの場合はアイテムID、Diamondの場合は空欄 |
| **cost_num** | コスト数。単発: `150`、10連: `1500`、チケット: `1` |
| **draw_count** | 実行回数。単発: `1`、10連: `10`、チケット: `1` |
| **cost_priority** | コスト優先度。数値が小さいほど優先(チケット: `2`、ダイヤ: `3`) |
| **release_key** | リリースキー。OprGachaと同じ値 |

#### 6.3 cost_priorityの設定

コスト優先度は、以下の順序で設定します。

```
1: 有償ダイヤ(PaidOnlyガチャ等)
2: チケット
3: ダイヤ(無償・有償共通)
```

クライアントは、cost_priorityが小さい順に使用可能なコストを選択します。

#### 6.4 作成例

**ピックアップガチャのコスト設定**:
```
ENABLE,id,opr_gacha_id,cost_type,cost_id,cost_num,draw_count,cost_priority,release_key
e,62,Pickup_jig_001,Item,ticket_glo_00003,1,1,2,202601010
e,63,Pickup_jig_001,Diamond,,150,1,3,202601010
e,64,Pickup_jig_001,Diamond,,1500,10,3,202601010
```

**説明**:
- id=62: チケット1枚で単発1回(優先度2)
- id=63: ダイヤ150個で単発1回(優先度3)
- id=64: ダイヤ1500個で10連(優先度3)

**注意**: idは既存のテーブルと重複しないように連番で採番します。

### 7. OprGachaDisplayUnitI18n シートの作成

#### 7.1 シートスキーマ

```
ENABLE,release_key,id,opr_gacha_id,mst_unit_id,language,sort_order,description
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。OprGachaと同じ値 |
| **id** | 表示ユニットの一意識別子。命名規則: `{opr_gacha_id}_{mst_unit_id}_{language}` |
| **opr_gacha_id** | ガチャID。OprGacha.idと対応 |
| **mst_unit_id** | ユニットID。表示対象キャラのID |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語 |
| **sort_order** | 表示順序。小さい順に表示(ピックアップキャラ: `1`、`2`) |
| **description** | キャラ説明文。ピックアップキャラの特徴や魅力を記載 |

#### 7.3 作成例

```
ENABLE,release_key,id,opr_gacha_id,mst_unit_id,language,sort_order,description
e,202601010,Pickup_jig_001_chara_jig_00401_ja,Pickup_jig_001,chara_jig_00401,ja,1,"体力の状態に応じて戦闘スタイルが変化する戦術キャラ！"
e,202601010,Pickup_jig_001_chara_jig_00501_ja,Pickup_jig_001,chara_jig_00501,ja,2,"サポート特化で味方を強化する支援キャラ！"
```

**注意**: ピックアップキャラのみ設定します(通常排出キャラは不要)。

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - OprGacha.id: `Pickup_{series_id}_{連番3桁}` または `Premium_{series_id}_{連番3桁}`
  - OprGacha.fixed_prize_group_id: `fixd_{opr_gacha_id}`
  - OprGachaI18n.id: `{opr_gacha_id}_{language}`
  - OprGachaPrize.id: `{group_id}_{連番}`
  - OprGachaDisplayUnitI18n.id: `{opr_gacha_id}_{mst_unit_id}_{language}`

- [ ] **リレーションの整合性**
  - `OprGacha.upper_group` が `OprGachaUpper.upper_group` に存在する
  - `OprGacha.prize_group_id` が `OprGachaPrize.group_id` に存在する
  - `OprGacha.fixed_prize_group_id` が `OprGachaPrize.group_id` に存在する
  - `OprGachaI18n.opr_gacha_id` が `OprGacha.id` に存在する
  - `OprGachaUseResource.opr_gacha_id` が `OprGacha.id` に存在する
  - `OprGachaDisplayUnitI18n.opr_gacha_id` が `OprGacha.id` に存在する
  - `OprGachaDisplayUnitI18n.mst_unit_id` が `MstUnit.id` に存在する

- [ ] **enum値の正確性**
  - gacha_type: Normal、Premium、Pickup、Free、Ticket、Festival、PaidOnly、Medal、Tutorial、StepUp
  - appearance_condition: Always、UserRank、QuestClear等
  - unlock_condition_type: None、TimeElapsed等
  - upper_type: Pickup、MaxRarity
  - cost_type: Diamond、Item
  - resource_type: Unit、Item
  - 大文字小文字が正確に一致している

- [ ] **開催期間の妥当性**
  - start_at < end_at
  - イベント全体の開催期間と整合している
  - 形式: `YYYY-MM-DD HH:MM:SS`

- [ ] **排出重みの合計**
  - prize_group_idの重み合計が約1,000,000
  - fixed_prize_group_idの重み合計が約25,000,000(R/N除外のため高め)

- [ ] **ピックアップフラグの正確性**
  - ピックアップキャラにpickup=1が設定されている
  - 通常排出キャラにpickup=0が設定されている

- [ ] **確定枠の設定**
  - multi_fixed_prize_count > 0 の場合、fixed_prize_group_idが設定されている
  - fixed_prize_group_idに対応するOprGachaPrizeが存在する

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語(ja)が必須で設定されている
  - 他言語(en、zh-CN、zh-TW)も設定されている

- [ ] **説明文の品質**
  - 誤字脱字がない
  - ピックアップキャラの魅力が伝わる内容

- [ ] **コスト設定の妥当性**
  - 単発150、10連1500の標準設定
  - チケットのcost_priorityが2(ダイヤより優先)

- [ ] **天井回数の妥当性**
  - 通常100回(業界標準)
  - 特殊なガチャの場合は設計書通り

## 出力フォーマット

最終的な出力は以下の6シート構成で行います。

### OprGacha シート

| ENABLE | id | gacha_type | upper_group | enable_ad_play | enable_add_ad_play_upper | ad_play_interval_time | multi_draw_count | multi_fixed_prize_count | daily_play_limit_count | total_play_limit_count | daily_ad_limit_count | total_ad_limit_count | prize_group_id | fixed_prize_group_id | appearance_condition | unlock_condition_type | unlock_duration_hours | start_at | end_at | display_information_id | dev-qa_display_information_id | display_gacha_caution_id | gacha_priority | release_key |
|--------|----|-----------|-----------|--------------|-----------------------|---------------------|----------------|----------------------|---------------------|---------------------|-------------------|-------------------|--------------|-------------------|-------------------|-------------------|-------------------|---------|-------|---------------------|---------------------------|----------------------|--------------|-------------|
| e | Pickup_jig_001 | Pickup | Pickup_jig_001 | | | __NULL__ | 10 | 1 | __NULL__ | __NULL__ | 0 | __NULL__ | Pickup_jig_001 | fixd_Pickup_jig_001 | Always | None | __NULL__ | 2026-01-16 12:00:00 | 2026-02-16 10:59:59 | 84b93bca-1b92-42df-9d6e-3a593fa76a69 | 84b93bca-1b92-42df-9d6e-3a593fa76a69 | 16d9cd62-8b4a-44c5-922a-6a6b7889ce06 | 66 | 202601010 |

### OprGachaI18n シート

| ENABLE | release_key | id | opr_gacha_id | language | name | description | max_rarity_upper_description | pickup_upper_description | fixed_prize_description | banner_url | logo_asset_key | logo_banner_url | gacha_background_color | gacha_banner_size |
|--------|------------|----|-----------|----|------|------------|--------------------------|----------------------|----------------------|----------|--------------|---------------|---------------------|-----------------|
| e | 202601010 | Pickup_jig_001_ja | Pickup_jig_001 | ja | 地獄楽 いいジャン祭ピックアップガシャ A | 「賊王 亜左 弔兵衛」と\n「山田浅ェ門 桐馬」の出現率UP中! | | ピックアップURキャラ1体確定! | SR以上1体確定 | jig_00001 | pickup_a_00001 | | Yellow | SizeL |

### OprGachaPrize シート(通常排出)

| ENABLE | id | group_id | resource_type | resource_id | resource_amount | weight | pickup | release_key |
|--------|----|---------|--------------|------------|----------------|--------|--------|-------------|
| e | Pickup_jig_001_1 | Pickup_jig_001 | Unit | chara_jig_00401 | 1 | 7020 | 1 | 202601010 |

### OprGachaPrize シート(確定枠排出)

| ENABLE | id | group_id | resource_type | resource_id | resource_amount | weight | pickup | release_key |
|--------|----|---------|--------------|------------|----------------|--------|--------|-------------|
| e | fixd_Pickup_jig_001_1 | fixd_Pickup_jig_001 | Unit | chara_jig_00401 | 1 | 175500 | 1 | 202601010 |

### OprGachaUpper シート

| ENABLE | id | upper_group | upper_type | count | release_key |
|--------|----|------------|----------|-------|-------------|
| e | 17 | Pickup_jig_001 | Pickup | 100 | 202601010 |

### OprGachaUseResource シート

| ENABLE | id | opr_gacha_id | cost_type | cost_id | cost_num | draw_count | cost_priority | release_key |
|--------|----|-----------|---------|----|--------|-----------|--------------|-------------|
| e | 62 | Pickup_jig_001 | Item | ticket_glo_00003 | 1 | 1 | 2 | 202601010 |

### OprGachaDisplayUnitI18n シート

| ENABLE | release_key | id | opr_gacha_id | mst_unit_id | language | sort_order | description |
|--------|------------|----|-----------|-----------|----|-----------|------------|
| e | 202601010 | Pickup_jig_001_chara_jig_00401_ja | Pickup_jig_001 | chara_jig_00401 | ja | 1 | 体力の状態に応じて戦闘スタイルが変化する戦術キャラ！ |

## 重要なポイント

- **6テーブル構成**: ガチャは基本設定、排出内容、天井、コスト、表示の6テーブルで構成されます
- **I18nは独立したシート**: OprGachaI18nとOprGachaDisplayUnitI18nは独立したシートとして作成
- **通常排出と確定枠排出**: OprGachaPrizeは通常排出(prize_group_id)と確定枠排出(fixed_prize_group_id)の2種類を作成
- **排出重みの計算**: レアリティと排出率から重みを計算(合計約1,000,000)
- **ピックアップキャラの重み**: ピックアップキャラは通常の4倍程度の重みを設定
- **天井設定**: 通常100回で確定(業界標準)
- **コスト優先度**: チケット(2) > ダイヤ(3)の順序
- **開催期間**: ガチャはイベント開始より早く開始することが多い
- **UUID管理**: display_information_idとdisplay_gacha_caution_idはStrapi管理
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
