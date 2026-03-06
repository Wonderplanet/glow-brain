# MstSeries 詳細説明

> CSVパス: `projects/glow-masterdata/MstSeries.csv`
> i18n CSVパス: `projects/glow-masterdata/MstSeriesI18n.csv`

---

## 概要

`MstSeries` は**GLOWゲーム内に登場するジャンプ+の漫画作品（シリーズ）を管理するテーブル**。`mst_series_i18n` は各シリーズの作品名・五十音絞り込みワードの多言語設定テーブル。

各漫画作品を識別するためのマスターデータであり、ユニット（キャラ）やクエストがどの作品に属するかの基準となる。ジャンプ+の公式URLやゲーム内アセットキーも管理する。

### ゲームへの影響

- **作品一覧画面**: `MstSeries` のレコードが作品ごとのカードとして表示される。`sort_order` などはないが、UI上での表示順は別途制御される。
- **絞り込み機能**: `prefix_word`（i18nテーブル）を使って、五十音行でシリーズを絞り込める（例: 「す」→ SPY×FAMILY）。
- **ジャンプ+連携**: `jump_plus_url` からゲーム内ブラウザでジャンプ+の当該作品エピソードに遷移できる。
- **アセット**: `asset_key` でキャラクターの作品アイコンや背景、`banner_asset_key` でバナー画像を指定する。

### テーブル連携図

```
MstSeries（作品マスター）
  ├─ id → MstSeriesI18n.mst_series_id（多言語テキスト）
  ├─ id → MstQuest.mst_series_id（クエストの作品紐付け）
  ├─ id → MstUnit.mst_series_id（ユニットの作品紐付け）
  └─ id → MstEvent.mst_series_id（イベントの作品紐付け）
```

---

## 全カラム一覧

### mst_series（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。作品略称（例: `spy`, `kai`） |
| `jump_plus_url` | varchar(255) | 不可 | - | ジャンプ+作品への公式URL |
| `asset_key` | varchar(255) | 不可 | - | アセットキー（作品アイコン等） |
| `banner_asset_key` | varchar(255) | 不可 | - | バナー用アセットキー |
| `release_key` | bigint | 不可 | 1 | リリースキー |

### MstSeriesI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_series_id` | varchar(255) | 不可 | - | 対応するシリーズID（`mst_series.id`） |
| `language` | enum | 不可 | - | 言語コード（現状 `ja` のみ） |
| `name` | varchar(255) | 不可 | - | 作品名（例: `SPY×FAMILY`、`怪獣８号`） |
| `prefix_word` | varchar(255) | 不可 | - | 五十音絞り込みワード（例: `す`, `か`） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

ユニーク制約: `(mst_series_id, language)` の組み合わせは一意。

---

## 命名規則 / IDの生成ルール

`MstSeries.id` は作品の略称3文字（英小文字）で命名する:

```
{作品略称3文字（英小文字）}
```

| id | 作品名 |
|----|--------|
| `spy` | SPY×FAMILY |
| `aka` | ラーメン赤猫 |
| `rik` | トマトイプーのリコピン |
| `dan` | ダンダダン |
| `gom` | 姫様"拷問"の時間です |
| `chi` | チェンソーマン |
| `mag` | 株式会社マジルミエ |
| `dos` | 道産子ギャルはなまらめんこい |
| `bat` | 忘却バッテリー |
| `kai` | 怪獣８号 |

`MstSeriesI18n.id` は `{mst_series_id}_{language}` で命名する（例: `spy_ja`）。

---

## 他テーブルとの連携

| 連携先テーブル | 結合キー | 用途 |
|-------------|--------|------|
| `MstSeriesI18n` | `MstSeriesI18n.mst_series_id = MstSeries.id` | 作品名・絞り込みワードの取得 |
| `MstQuest` | `MstQuest.mst_series_id = MstSeries.id` | 作品に属するクエスト一覧 |
| `MstUnit` | `MstUnit.mst_series_id = MstSeries.id` | 作品に属するユニット一覧 |
| `MstEvent` | `MstEvent.mst_series_id = MstSeries.id` | 作品に関連するイベント一覧 |

---

## 実データ例

### パターン1: SPY×FAMILYシリーズ

```csv
ENABLE,id,asset_key,release_key,jump_plus_url,banner_asset_key
e,spy,spy,202509010,https://shonenjumpplus.com/episode/10834108156648240735,spy
```

```csv
ENABLE,release_key,id,mst_series_id,language,name,prefix_word
e,202509010,spy_ja,spy,ja,SPY×FAMILY,す
```

- `id = spy` という短い略称で管理
- `prefix_word = す` → 五十音「サ行」で絞り込み対象

### パターン2: 姫様"拷問"の時間です

```csv
ENABLE,id,asset_key,release_key,jump_plus_url,banner_asset_key
e,gom,gom,202509010,https://shonenjumpplus.com/episode/10834108156649530410,gom
```

```csv
ENABLE,release_key,id,mst_series_id,language,name,prefix_word
e,202509010,gom_ja,gom,ja,姫様"拷問"の時間です,ひ
```

- `prefix_word = ひ` → 五十音「ハ行」で絞り込み対象

---

## 設定時のポイント

1. **`id` は3文字英小文字略称で命名する**。他テーブル（MstQuest, MstUnit等）からの参照キーになるため、一度決めたら変更しない。
2. **`jump_plus_url` はジャンプ+の第1話エピソードURLを設定するのが慣例**。ゲーム内ブラウザでの表示に使われる。
3. **`asset_key` と `banner_asset_key` はアセットバンドルのキー名**。対応するアセットが実装済みであることを確認してから設定する。
4. **`prefix_word` は作品名の読み仮名の先頭文字（1文字）を設定する**。「SPY×FAMILY」の読みは「スパイファミリー」なので `す` を設定するなど、読み方に注意する。
5. **i18nレコードはシリーズ本体と必ずセットで作成する**。`MstSeries` を追加したら `MstSeriesI18n` も対応する言語分作成する。
6. **新作品追加時は `release_key` を正しく設定する**。公開予定のリリースキーに合わせる。
7. **`MstSeries.id`（series）は単複同形のため末尾の `s` を除去しない**。ファイル名は `MstSeries.csv`、テーブル名は `mst_series` となる（注意: `mst_series` はすでに単数形に相当）。
