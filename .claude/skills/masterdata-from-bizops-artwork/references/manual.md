# 原画・エンブレム マスタデータ設定手順書

## 概要

原画とエンブレムのマスタデータ作成手順を記載します。
本手順書に従うことで、ゲーム内でエラーが発生しない正確なマスタデータを作成できます。

## 対象テーブル

原画・エンブレムのマスタデータは、以下の7テーブル構成で作成します。

**原画情報**:
- **MstArtwork** - 原画の基本情報（レアリティ、拠点追加HP等）
- **MstArtworkI18n** - 原画名・説明文（多言語対応）
- **MstArtworkFragment** - 原画の欠片情報（ドロップ率、レアリティ等）
- **MstArtworkFragmentI18n** - 原画の欠片名（多言語対応）
- **MstArtworkFragmentPosition** - 原画の欠片の配置位置（1~16）

**エンブレム情報**:
- **MstEmblem** - エンブレムの基本情報（タイプ、シリーズID等）
- **MstEmblemI18n** - エンブレム名・説明文（多言語対応）

**重要**: 各I18nテーブルは独立したシートとして作成します。

## 作成フロー

### 1. 仕様書の確認

原画・エンブレムの設計書から以下の情報を抽出します。

**原画に必要な情報**:
- 原画ID（例: `artwork_event_jig_0001`）
- シリーズID（例: `jig`）
- 原画タイトル（最大40文字）
- 原画説明文（最大255文字）
- レアリティ（N、R、SR、SSR、UR）
- 拠点追加HP（完成時にゲートに加算するHP）
- 欠片の数（通常16個）
- 欠片のドロップグループID
- 欠片のドロップ率（通常100%）

**エンブレムに必要な情報**:
- エンブレムID（例: `emblem_event_jig_00001`）
- エンブレムタイプ（Event、Series）
- シリーズID（例: `jig`）
- エンブレム名（最大255文字）
- エンブレム説明文（最大255文字）
- アセットキー

### 2. MstArtwork シートの作成

#### 2.1 シートスキーマ

このシートには、ENABLE行とデータ行が含まれます。

**ENABLEと列名行** - カラム名を示します。

```
ENABLE,id,mst_series_id,outpost_additional_hp,asset_key,sort_order,rarity,release_key
```

#### 2.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | 原画の一意識別子。命名規則: `artwork_{category}_{series_id}_{連番4桁}` |
| **mst_series_id** | シリーズID。例: `jig`（地獄楽）、`osh`（推しの子）、`kai`（怪獣8号） |
| **outpost_additional_hp** | 完成時にゲートに加算するHP。通常は `100` |
| **asset_key** | アセットキー。通常はMstArtwork.idと同じ値（`event_jig_0001`のようにプレフィックスを除いたもの） |
| **sort_order** | 表示順序。2桁のゼロパディング（例: `01`、`02`） |
| **rarity** | レアリティ。下記の「rarity設定一覧」を参照 |
| **release_key** | リリースキー。例: `202601010` |

#### 2.3 rarity設定一覧

| rarity | 説明 | レアリティ |
|--------|------|-----------|
| **UR** | Ultra Rare | 最高レアリティ |
| **SSR** | Super Super Rare | 非常に高レアリティ |
| **SR** | Super Rare | 高レアリティ |
| **R** | Rare | 中レアリティ |
| **N** | Normal | 通常レアリティ |

#### 2.4 ID採番ルール

原画のIDは、以下の形式で採番します。

```
artwork_{category}_{series_id}_{連番4桁}
```

**パラメータ**:
- `category`: 原画のカテゴリ
  - event = イベント原画
  - series = シリーズ原画
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
- `連番4桁`: カテゴリ内で0001からゼロパディング

**採番例**:
```
artwork_event_jig_0001   (地獄楽 イベント原画1)
artwork_event_jig_0002   (地獄楽 イベント原画2)
artwork_series_osh_0001  (推しの子 シリーズ原画1)
```

#### 2.5 作成例

```
ENABLE,id,mst_series_id,outpost_additional_hp,asset_key,sort_order,rarity,release_key
e,artwork_event_jig_0001,jig,100,event_jig_0001,01,SSR,202601010
e,artwork_event_jig_0002,jig,100,event_jig_0002,02,SSR,202601010
```

### 3. MstArtworkI18n シートの作成

#### 3.1 シートスキーマ

```
ENABLE,release_key,id,mst_artwork_id,language,name,description
```

#### 3.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstArtworkと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_artwork_id}_{language}` |
| **mst_artwork_id** | 原画ID。MstArtwork.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **name** | 原画名（最大40文字） |
| **description** | 原画説明文（最大255文字） |

#### 3.3 作成例

```
ENABLE,release_key,id,mst_artwork_id,language,name,description
e,202601010,artwork_event_jig_0001_ja,artwork_event_jig_0001,ja,必ず生きて帰る,"死罪となった""がらんの画眉丸""は、死を目前に妻の言葉を思い出す。「人の心は、そんなに簡単に死なないわ」。里に残した妻がかけてくれた言葉だ。妻との「普通の暮らし」を手に入れるため、画眉丸は謎多き島・神仙郷へ向かう。そして心に強く誓う「必ず生きて帰る」と。"
e,202601010,artwork_event_jig_0002_ja,artwork_event_jig_0002,ja,兄は弟の道標だ！！,"立場こそ死罪人と首切り役人だが、亜左 弔兵衛と山田浅ェ門 桐馬は、紛れもなく兄弟である。二人は、壮絶な幼少期を生き抜いてきた。親を失い路頭に迷う日々に、泣きじゃくる弟へ兄は言った。「何が正しいかわからねぇなら、オレだけを信じろ！」その言葉が示すように、二人の絆は成長した今も昔も変わらない。兄は弟を導き、弟は兄を信じ続けている。"
```

**注意**: 説明文に二重引用符（`"`）が含まれる場合は、CSVの仕様に従ってエスケープが必要です（`""`として記述）。

### 4. MstArtworkFragment シートの作成

#### 4.1 シートスキーマ

```
ENABLE,release_key,id,mst_artwork_id,drop_group_id,drop_percentage,rarity,asset_num
```

#### 4.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstArtworkと同じ値 |
| **id** | 原画の欠片の一意識別子。命名規則: `artwork_fragment_{category}_{series_id}_{連番5桁}` |
| **mst_artwork_id** | 原画ID。MstArtwork.idと対応 |
| **drop_group_id** | ステージのドロップ単位。同じステージで複数の欠片が同時にドロップする場合は同じIDを設定。非ドロップの場合は空欄 |
| **drop_percentage** | ドロップ率（0~100）。通常は `100`。非ドロップの場合は空欄 |
| **rarity** | レアリティ。MstArtworkと同じ値を設定することが多い |
| **asset_num** | アセット番号（1~16）。原画のどの欠片かを示す |

#### 4.3 ID採番ルール

```
artwork_fragment_{category}_{series_id}_{連番5桁}
```

**パラメータ**:
- `category`: MstArtwork.idと同じカテゴリ
- `series_id`: MstArtwork.idと同じシリーズID
- `連番5桁`: 原画ごとに00001からゼロパディング

**採番例**:
```
artwork_fragment_event_jig_00001   (地獄楽 イベント原画1の欠片1)
artwork_fragment_event_jig_00002   (地獄楽 イベント原画1の欠片2)
...
artwork_fragment_event_jig_00016   (地獄楽 イベント原画1の欠片16)
artwork_fragment_event_jig_00101   (地獄楽 イベント原画2の欠片1)
```

**連番のルール**:
- 1つ目の原画: 00001~00016（欠片1~16）
- 2つ目の原画: 00101~00116（欠片1~16）
- 3つ目の原画: 00201~00216（欠片1~16）

#### 4.4 作成例

```
ENABLE,release_key,id,mst_artwork_id,drop_group_id,drop_percentage,rarity,asset_num
e,202601010,artwork_fragment_event_jig_00001,artwork_event_jig_0001,event_jig_a_0001,100,SSR,7
e,202601010,artwork_fragment_event_jig_00002,artwork_event_jig_0001,event_jig_a_0001,100,SSR,5
e,202601010,artwork_fragment_event_jig_00003,artwork_event_jig_0001,event_jig_a_0002,100,SSR,16
e,202601010,artwork_fragment_event_jig_00004,artwork_event_jig_0001,event_jig_a_0002,100,SSR,11
e,202601010,artwork_fragment_event_jig_00005,artwork_event_jig_0001,event_jig_a_0003,100,SSR,2
e,202601010,artwork_fragment_event_jig_00006,artwork_event_jig_0001,event_jig_a_0003,100,SSR,9
e,202601010,artwork_fragment_event_jig_00007,artwork_event_jig_0001,event_jig_a_0003,100,SSR,13
e,202601010,artwork_fragment_event_jig_00008,artwork_event_jig_0001,event_jig_a_0004,100,SSR,8
e,202601010,artwork_fragment_event_jig_00009,artwork_event_jig_0001,event_jig_a_0004,100,SSR,4
e,202601010,artwork_fragment_event_jig_00010,artwork_event_jig_0001,event_jig_a_0004,100,SSR,3
e,202601010,artwork_fragment_event_jig_00011,artwork_event_jig_0001,event_jig_a_0005,100,SSR,10
e,202601010,artwork_fragment_event_jig_00012,artwork_event_jig_0001,event_jig_a_0005,100,SSR,14
e,202601010,artwork_fragment_event_jig_00013,artwork_event_jig_0001,event_jig_a_0005,100,SSR,6
e,202601010,artwork_fragment_event_jig_00014,artwork_event_jig_0001,event_jig_a_0006,100,SSR,12
e,202601010,artwork_fragment_event_jig_00015,artwork_event_jig_0001,event_jig_a_0006,100,SSR,1
e,202601010,artwork_fragment_event_jig_00016,artwork_event_jig_0001,event_jig_a_0006,100,SSR,15
```

**ポイント**:
- 16個の欠片を6つのステージに分散配置
- 同じ`drop_group_id`を持つ欠片は同じステージで同時にドロップ
- `asset_num`は1~16のランダムな順序（実際の原画の配置に対応）

### 5. MstArtworkFragmentI18n シートの作成

#### 5.1 シートスキーマ

```
ENABLE,release_key,id,mst_artwork_fragment_id,language,name
```

#### 5.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstArtworkFragmentと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_artwork_fragment_id}_{language}` |
| **mst_artwork_fragment_id** | 原画の欠片ID。MstArtworkFragment.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **name** | 原画の欠片名（最大15文字）。通常は「原画のかけら{asset_num}」 |

#### 5.3 作成例

```
ENABLE,release_key,id,mst_artwork_fragment_id,language,name
e,202601010,artwork_fragment_event_jig_00001_ja,artwork_fragment_event_jig_00001,ja,原画のかけら7
e,202601010,artwork_fragment_event_jig_00002_ja,artwork_fragment_event_jig_00002,ja,原画のかけら5
e,202601010,artwork_fragment_event_jig_00003_ja,artwork_fragment_event_jig_00003,ja,原画のかけら16
```

**注意**: 名称の数字はMstArtworkFragment.asset_numと対応させます。

### 6. MstArtworkFragmentPosition シートの作成

#### 6.1 シートスキーマ

```
ENABLE,release_key,id,mst_artwork_fragment_id,position
```

#### 6.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstArtworkFragmentと同じ値 |
| **id** | 位置情報の一意識別子。MstArtworkFragment.idと同じ値 |
| **mst_artwork_fragment_id** | 原画の欠片ID。MstArtworkFragment.idと対応 |
| **position** | 表示位置（1~16）。原画を4×4のグリッドに分割した際の位置番号 |

#### 6.3 position配置の考え方

原画は4×4のグリッド（16個のマス）で構成されます。positionは以下のように対応します。

```
 1  |  2  |  3  |  4
----+-----+-----+-----
 5  |  6  |  7  |  8
----+-----+-----+-----
 9  | 10  | 11  | 12
----+-----+-----+-----
13  | 14  | 15  | 16
```

**重要**: MstArtworkFragment.asset_numとpositionは同じ値を設定するのが一般的です。

#### 6.4 作成例

```
ENABLE,release_key,id,mst_artwork_fragment_id,position
e,202601010,artwork_fragment_event_jig_00001,artwork_fragment_event_jig_00001,7
e,202601010,artwork_fragment_event_jig_00002,artwork_fragment_event_jig_00002,5
e,202601010,artwork_fragment_event_jig_00003,artwork_fragment_event_jig_00003,16
e,202601010,artwork_fragment_event_jig_00004,artwork_fragment_event_jig_00004,11
e,202601010,artwork_fragment_event_jig_00005,artwork_fragment_event_jig_00005,2
e,202601010,artwork_fragment_event_jig_00006,artwork_fragment_event_jig_00006,9
e,202601010,artwork_fragment_event_jig_00007,artwork_fragment_event_jig_00007,13
e,202601010,artwork_fragment_event_jig_00008,artwork_fragment_event_jig_00008,8
e,202601010,artwork_fragment_event_jig_00009,artwork_fragment_event_jig_00009,4
e,202601010,artwork_fragment_event_jig_00010,artwork_fragment_event_jig_00010,3
e,202601010,artwork_fragment_event_jig_00011,artwork_fragment_event_jig_00011,10
e,202601010,artwork_fragment_event_jig_00012,artwork_fragment_event_jig_00012,14
e,202601010,artwork_fragment_event_jig_00013,artwork_fragment_event_jig_00013,6
e,202601010,artwork_fragment_event_jig_00014,artwork_fragment_event_jig_00014,12
e,202601010,artwork_fragment_event_jig_00015,artwork_fragment_event_jig_00015,1
e,202601010,artwork_fragment_event_jig_00016,artwork_fragment_event_jig_00016,15
```

### 7. MstEmblem シートの作成

#### 7.1 シートスキーマ

```
ENABLE,id,emblemType,mstSeriesId,assetKey,release_key
```

#### 7.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **id** | エンブレムの一意識別子。命名規則: `emblem_{category}_{series_id}_{連番5桁}` |
| **emblemType** | エンブレムのタイプ。下記の「emblemType設定一覧」を参照 |
| **mstSeriesId** | シリーズID。例: `jig`（地獄楽）、`osh`（推しの子）、`kai`（怪獣8号） |
| **assetKey** | アセットキー。エンブレム画像ファイルのキー |
| **release_key** | リリースキー。例: `202601010` |

#### 7.3 emblemType設定一覧

| emblemType | 説明 | 使用例 |
|-----------|------|--------|
| **Event** | イベントエンブレム | イベント参加報酬、降臨バトルランキング報酬 |
| **Series** | シリーズエンブレム | 特定作品のコレクション報酬 |

#### 7.4 ID採番ルール

```
emblem_{category}_{series_id}_{連番5桁}
```

**パラメータ**:
- `category`: エンブレムのカテゴリ
  - event = イベントエンブレム
  - adventbattle = 降臨バトルエンブレム
  - series = シリーズエンブレム
- `series_id`: 3文字のシリーズ識別子
  - jig = 地獄楽
  - osh = 推しの子
  - kai = 怪獣8号
- `連番5桁`: カテゴリ内で00001からゼロパディング

**降臨バトルエンブレムの特別な採番**:
```
emblem_adventbattle_{series_id}_{season}_{連番5桁}
```

**採番例**:
```
emblem_event_jig_00001                         (地獄楽 イベントエンブレム1)
emblem_adventbattle_jig_season01_00001         (地獄楽 降臨バトルシーズン1 エンブレム1)
emblem_adventbattle_jig_season01_00002         (地獄楽 降臨バトルシーズン1 エンブレム2)
emblem_series_jig_00001                        (地獄楽 シリーズエンブレム1)
```

#### 7.5 作成例

```
ENABLE,id,emblemType,mstSeriesId,assetKey,release_key
e,emblem_event_jig_00001,Event,jig,event_jig_00001,202601010
e,emblem_adventbattle_jig_season01_00001,Event,jig,adventbattle_jig_season01_00001,202601010
e,emblem_adventbattle_jig_season01_00002,Event,jig,adventbattle_jig_season01_00002,202601010
e,emblem_adventbattle_jig_season01_00003,Event,jig,adventbattle_jig_season01_00003,202601010
e,emblem_adventbattle_jig_season01_00004,Event,jig,adventbattle_jig_season01_00004,202601010
e,emblem_adventbattle_jig_season01_00005,Event,jig,adventbattle_jig_season01_00005,202601010
e,emblem_adventbattle_jig_season01_00006,Event,jig,adventbattle_jig_season01_00006,202601010
```

**注意**: 降臨バトルエンブレムは通常ランキング順位に応じて複数作成します（1位、2位、3位、4~50位、51~300位、301~1,000位など）。

### 8. MstEmblemI18n シートの作成

#### 8.1 シートスキーマ

```
ENABLE,release_key,id,mst_emblem_id,language,name,description
```

#### 8.2 各カラムの設定ルール

| カラム名 | 設定ルール |
|---------|-----------|
| **ENABLE** | 固定値: `e` (有効化) |
| **release_key** | リリースキー。MstEmblemと同じ値 |
| **id** | I18nの一意識別子。命名規則: `{mst_emblem_id}_{language}` |
| **mst_emblem_id** | エンブレムID。MstEmblem.idと対応 |
| **language** | 言語コード。`ja`: 日本語、`en`: 英語、`zh-CN`: 中国語（簡体字）、`zh-TW`: 中国語（繁体字） |
| **name** | エンブレム名（最大255文字） |
| **description** | フレーバーテキスト（最大255文字） |

#### 8.3 作成例

```
ENABLE,release_key,id,mst_emblem_id,language,name,description
e,202601010,emblem_event_jig_00001_ja,emblem_event_jig_00001,ja,神仙郷,仙薬探しのため、死罪人と打ち首執行人たちが上陸した秘境の島
e,202601010,emblem_adventbattle_jig_season01_00001_ja,emblem_adventbattle_jig_season01_00001,ja,罪人(1位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』1位の証"
e,202601010,emblem_adventbattle_jig_season01_00002_ja,emblem_adventbattle_jig_season01_00002,ja,罪人(2位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』2位の証"
e,202601010,emblem_adventbattle_jig_season01_00003_ja,emblem_adventbattle_jig_season01_00003,ja,罪人(3位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』3位の証"
e,202601010,emblem_adventbattle_jig_season01_00004_ja,emblem_adventbattle_jig_season01_00004,ja,罪人(4~50位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』4~50位の証"
e,202601010,emblem_adventbattle_jig_season01_00005_ja,emblem_adventbattle_jig_season01_00005,ja,罪人(51~300位),"2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』51~300位の証"
e,202601010,emblem_adventbattle_jig_season01_00006_ja,emblem_adventbattle_jig_season01_00006,ja,"罪人(301~1,000位)","2026年1月開催降臨バトル『まるで 悪夢を見ているようだ』301~1,000位の証"
```

## データ整合性のチェック

マスタデータ作成後、以下の項目を確認してください。

### 必須チェック項目

- [ ] **ヘッダーの列順が正しいか**
  - スキーマファイルと完全一致している

- [ ] **IDの一意性**
  - すべてのidが一意である
  - 他のリリースのidと重複していない

- [ ] **ID採番ルール**
  - MstArtwork.id: `artwork_{category}_{series_id}_{連番4桁}`
  - MstArtworkFragment.id: `artwork_fragment_{category}_{series_id}_{連番5桁}`
  - MstEmblem.id: `emblem_{category}_{series_id}_{連番5桁}` または `emblem_adventbattle_{series_id}_{season}_{連番5桁}`
  - I18n系テーブルのid: `{親テーブルid}_{language}`

- [ ] **リレーションの整合性**
  - `MstArtworkI18n.mst_artwork_id` が `MstArtwork.id` に存在する
  - `MstArtworkFragment.mst_artwork_id` が `MstArtwork.id` に存在する
  - `MstArtworkFragmentI18n.mst_artwork_fragment_id` が `MstArtworkFragment.id` に存在する
  - `MstArtworkFragmentPosition.mst_artwork_fragment_id` が `MstArtworkFragment.id` に存在する
  - `MstEmblemI18n.mst_emblem_id` が `MstEmblem.id` に存在する

- [ ] **enum値の正確性**
  - MstArtwork.rarity: N、R、SR、SSR、UR
  - MstArtworkFragment.rarity: N、R、SR、SSR、UR
  - MstEmblem.emblemType: Event、Series
  - language: ja、en、zh-CN、zh-TW
  - 大文字小文字が正確に一致している

- [ ] **欠片の完全性**
  - 1つの原画につき16個の欠片が存在する
  - MstArtworkFragment、MstArtworkFragmentI18n、MstArtworkFragmentPositionの3テーブルで同じ欠片IDが存在する
  - asset_numとpositionは1~16の値を持ち、すべての値が重複なく存在する

- [ ] **数値の妥当性**
  - outpost_additional_hpが正の整数である（通常100）
  - sort_orderが適切な順序である
  - drop_percentageが0~100の範囲内である
  - positionが1~16の範囲内である
  - asset_numが1~16の範囲内である

### 推奨チェック項目

- [ ] **命名規則の統一**
  - idのプレフィックスがカテゴリとシリーズIDと一致している

- [ ] **I18n設定の完全性**
  - 日本語（ja）が必須で設定されている
  - 他言語（en、zh-CN、zh-TW）も設定されている

- [ ] **テキストの品質**
  - 誤字脱字がない
  - 文字数制限内に収まっている（name: 40文字、description: 255文字）
  - 特殊文字（二重引用符、カンマ等）が適切にエスケープされている

- [ ] **ドロップグループの妥当性**
  - 同じステージで複数の欠片がドロップする場合、同じdrop_group_idが設定されている
  - drop_group_idが対応するステージIDと整合性がある

## 出力フォーマット

最終的な出力は以下の7シート構成で行います。

### MstArtwork シート

| ENABLE | id | mst_series_id | outpost_additional_hp | asset_key | sort_order | rarity | release_key |
|--------|----|--------------|-----------------------|-----------|-----------|--------|-------------|
| e | artwork_event_jig_0001 | jig | 100 | event_jig_0001 | 01 | SSR | 202601010 |
| e | artwork_event_jig_0002 | jig | 100 | event_jig_0002 | 02 | SSR | 202601010 |

### MstArtworkI18n シート

| ENABLE | release_key | id | mst_artwork_id | language | name | description |
|--------|-------------|----|---------------|----------|------|-------------|
| e | 202601010 | artwork_event_jig_0001_ja | artwork_event_jig_0001 | ja | 必ず生きて帰る | 死罪となった「がらんの画眉丸」は... |

### MstArtworkFragment シート

| ENABLE | release_key | id | mst_artwork_id | drop_group_id | drop_percentage | rarity | asset_num |
|--------|-------------|----|--------------|--------------|-----------------|----|-----------|
| e | 202601010 | artwork_fragment_event_jig_00001 | artwork_event_jig_0001 | event_jig_a_0001 | 100 | SSR | 7 |

### MstArtworkFragmentI18n シート

| ENABLE | release_key | id | mst_artwork_fragment_id | language | name |
|--------|-------------|----|-----------------------|----------|------|
| e | 202601010 | artwork_fragment_event_jig_00001_ja | artwork_fragment_event_jig_00001 | ja | 原画のかけら7 |

### MstArtworkFragmentPosition シート

| ENABLE | release_key | id | mst_artwork_fragment_id | position |
|--------|-------------|----|-----------------------|----------|
| e | 202601010 | artwork_fragment_event_jig_00001 | artwork_fragment_event_jig_00001 | 7 |

### MstEmblem シート

| ENABLE | id | emblemType | mstSeriesId | assetKey | release_key |
|--------|----|-----------|-----------|---------||-------------|
| e | emblem_event_jig_00001 | Event | jig | event_jig_00001 | 202601010 |

### MstEmblemI18n シート

| ENABLE | release_key | id | mst_emblem_id | language | name | description |
|--------|-------------|----|--------------|---------||------|-------------|
| e | 202601010 | emblem_event_jig_00001_ja | emblem_event_jig_00001 | ja | 神仙郷 | 仙薬探しのため、死罪人と打ち首執行人たちが上陸した秘境の島 |

## 重要なポイント

- **7テーブル構成**: 原画5テーブル、エンブレム2テーブルの構成
- **I18nは独立したシート**: 各I18nテーブルは独立したシートとして作成
- **16個の欠片**: 1つの原画につき16個の欠片を作成（4×4グリッド）
- **欠片の3テーブル**: MstArtworkFragment、MstArtworkFragmentI18n、MstArtworkFragmentPositionは連動して作成
- **ドロップグループの設計**: 欠片をどのステージでドロップさせるかを設計
- **降臨バトルエンブレム**: ランキング順位に応じて複数のエンブレムを作成（通常6個）
- **外部キー整合性の徹底**: すべてのリレーションが正しく設定されていることを確認
- **asset_numとpositionの一致**: 通常は同じ値を設定

## 設計書との対応

### インプット資料

1. **ヒーロー手順書**: テンプレートとして参照
2. **マッピング分析**: 原画・エンブレムの対応関係を確認
3. **実際のマスタデータ（地獄楽）**: 実例として参照
4. **DBスキーマ**: テーブル定義を確認

### アウトプット

- `/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/manuals/artwork-emblem/` に保存
- 運営仕様書から原画・エンブレムのマスタデータを作成する際の手順書として使用

## 参考情報

### 原画の配置位置（4×4グリッド）

```
位置1  位置2  位置3  位置4
位置5  位置6  位置7  位置8
位置9  位置10 位置11 位置12
位置13 位置14 位置15 位置16
```

### 降臨バトルエンブレムのランキング区分例

| エンブレム | 対象順位 |
|-----------|---------|
| エンブレム1 | 1位 |
| エンブレム2 | 2位 |
| エンブレム3 | 3位 |
| エンブレム4 | 4~50位 |
| エンブレム5 | 51~300位 |
| エンブレム6 | 301~1,000位 |

### 欠片のドロップ設計例

| ドロップグループ | 欠片数 | asset_num |
|----------------|-------|----------|
| event_jig_a_0001 | 2個 | 7, 5 |
| event_jig_a_0002 | 2個 | 16, 11 |
| event_jig_a_0003 | 3個 | 2, 9, 13 |
| event_jig_a_0004 | 3個 | 8, 4, 3 |
| event_jig_a_0005 | 3個 | 10, 14, 6 |
| event_jig_a_0006 | 3個 | 12, 1, 15 |

**合計**: 6ステージで16個の欠片をすべてドロップ可能
