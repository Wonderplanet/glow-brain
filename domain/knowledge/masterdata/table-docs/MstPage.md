# MstPage 詳細解説

## 1. 概要

`MstPage` はインゲームバトルのフィールドを構成する**ページ定義テーブル**です。

GLOW のインゲームフィールドは **ページ（MstPage）→ コマライン（MstKomaLine）→ コマ（キャラ/オブジェクト）** の3階層で構成されます。`MstPage` はその最上位にあたり、ステージで使用するフィールドの「識別子」として機能します。MstPage 自体は ID と release_key のみを持つシンプルなテーブルで、フィールドの実質的な内容（コマ配置・エフェクト）は子テーブルの `MstKomaLine` で定義されます。

| 項目 | 内容 |
|------|------|
| **DBテーブル名** | `mst_pages` |
| **CSVファイル名** | `MstPage.csv` |
| **クライアントクラス** | `MstPageData.cs` |
| **総レコード数** | 521件 |
| **主なユースケース** | インゲームバトルフィールドの識別子定義 |

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL | デフォルト値 | 説明 |
|---------|-----|------|------------|------|
| `ENABLE` | varchar | - | - | 有効フラグ（CSVでは `e` が入力される） |
| `id` | varchar(255) | NO | なし | ページの一意識別子（主キー）。ステージ種別と内容を示す文字列 |
| `release_key` | int | NO | `1` | リリースキー（マスタデータ反映制御。`MstReleaseKey` テーブルと対応） |

---

## 3. 主要な enum / フラグの解説

`MstPage` テーブル自体には enum カラムは存在しません。フィールドのエフェクト種別などは子テーブル `MstKomaLine` 側で定義されます。

### ENABLE フラグ

| 値 | 説明 |
|----|------|
| `e` | 有効（enabled）。通常すべてのレコードに設定される |

---

## 4. 命名規則 / IDの生成ルール

`id` は**ステージ種別・コンテンツ種別・対象キャラ・連番**を組み合わせた snake_case の文字列です。

### id の命名パターン

```
{ステージ種別}_{対象キャラ略称}_{連番}
```

実データで確認されている主なパターンは以下のとおりです。

| パターン | 例 | 説明 |
|---------|-----|------|
| `tutorial` | `tutorial`, `tutorial_2` | チュートリアルステージ |
| `default_pvp` | `default_pvp` | PvPのデフォルトフィールド |
| `pvp_{バージョン}_{連番}` | `pvp_202509010_01` | 期間指定PvPフィールド |
| `pvp_{キャラ略称}_{連番}` | `pvp_spy_01`, `pvp_dan_01` | キャラ別PvPフィールド |
| `enhance_{連番}` | `enhance_00001` | 強化クエストフィールド |
| `normal_{キャラ略称}_{連番}` | `normal_gom_00001` | 通常難易度クエストフィールド |
| `hard_{キャラ略称}_{連番}` | `hard_gom_00001` | ハード難易度クエストフィールド |
| `veryhard_{キャラ略称}_{連番}` | `veryhard_gom_00001` | ベリーハード難易度クエストフィールド |
| `event_{キャラ略称}_{種別}_{連番}` | `event_kai1_savage_00001` | イベントクエストフィールド |
| `raid_{キャラ略称}_{連番}` | `raid_gom_00001` | レイドクエストフィールド |
| `page_develop_{連番}` | `page_develop_001` | 開発用フィールド |
| `plan_test_{名前}` | `plan_test_stage001` | 企画テスト用フィールド |
| `test_{種別}` | `test_pvp` | テスト用フィールド |

### イベント種別サフィックス

イベントフィールドのIDには、コンテンツ種別を示すサフィックスが付きます。

| サフィックス | 説明 |
|-------------|------|
| `_savage_` | サベージバトル |
| `_challenge_` | チャレンジクエスト |
| `_charaget_` | キャラ獲得クエスト |
| `_1day_` | 1日限定クエスト |

### 連番フォーマット

本番データは `00001` のように**5桁ゼロパディング**を採用します。テスト・開発用データは `001`（3桁）や番号なしのケースもあります。

---

## 5. 他テーブルとの連携

### テーブル関連図

```
MstPage (1)
    └── MstKomaLine (N)  ← mst_page_id で結合

MstInGame (N)
    └── mst_page_id → MstPage.id
```

### 参照関係

| テーブル | 結合カラム | 説明 |
|---------|----------|------|
| `MstKomaLine` | `MstKomaLine.mst_page_id` → `MstPage.id` | ページを構成するコマラインの定義。1ページに複数のコマラインが紐づく |
| `MstInGame` | `MstInGame.mst_page_id` → `MstPage.id` | バトルセッションのフィールド指定。ステージのインゲーム設定からページを参照する |

### クライアント実装での使用箇所

| ファイル | 用途 |
|---------|------|
| `MasterDataRepository.cs` | `GetPage(MasterDataId id)` で ID 指定検索を提供。`MstKomaLine` と GroupJoin して `MstPageModel` を生成する |
| `PageDataTranslator.cs` | `MstPageData` と `MstKomaLineData` から `MstPageModel` に変換する |
| `MstPageModel.cs` | ページのドメインモデル。コマ総数・ライン高さ・座標計算などのロジックを保持する |
| `IMstPageDataRepository.cs` | リポジトリインターフェース定義 |
| `MstInGameData.cs` | `MstPageId` フィールドを通じて MstPage を参照する |
| `StageDataTranslator.cs` | ステージデータ変換時に MstPage を参照する |

---

## 6. 実データ例

CSVファイル: `projects/glow-masterdata/MstPage.csv`（521件）

| ENABLE | id | release_key |
|--------|-----|------------|
| e | tutorial | 202509010 |
| e | tutorial_2 | 202509010 |
| e | default_pvp | 202509010 |
| e | pvp_202509010_01 | 202509010 |
| e | enhance_00001 | 202509010 |
| e | normal_gom_00001 | 202509010 |
| e | hard_gom_00001 | 202509010 |
| e | veryhard_gom_00001 | 202509010 |
| e | event_kai1_savage_00001 | 202509010 |
| e | pvp_spy_01 | 202509010 |

### release_key の値一覧（実データより）

| release_key | 意味 |
|-------------|------|
| `202509010` | 2025年9月第1弾（初期リリース時のデータ） |
| `202510010` ～ `202603025` | 各月・各弾のリリース時に追加されたデータ |
| `999999999` | 開発・テスト用データ（本番配信対象外） |

### ID種別ごとのレコード数（実データより）

| ID種別 | 件数 | 主な用途 |
|--------|------|---------|
| event_charaget | 142 | イベントキャラ獲得クエスト |
| hard | 78 | ハード難易度クエスト |
| veryhard | 78 | ベリーハード難易度クエスト |
| normal | 78 | 通常難易度クエスト |
| event_challenge | 48 | イベントチャレンジクエスト |
| event_savage | 31 | サベージバトル |
| pvp | 21 | PvPフィールド |
| plan (テスト用) | 14 | 企画テスト用 |
| event_1day | 13 | 1日限定イベント |
| raid | 11 | レイドバトル |
| tutorial | 2 | チュートリアル |
| page (開発用) | 2 | 開発用 |
| その他 | 3 | enhance, test, default_pvp |

---

## 7. 設定時のポイント

### MstKomaLine を必ず紐付ける

`MstPage` 単体ではフィールドとして機能しません。MstPage のレコードを追加した場合は、必ず同一の `id` を `mst_page_id` に持つ `MstKomaLine` レコードを1件以上作成してください。

クライアントの `CheckCreateMstPageModelsError()` は、`MstKomaLine` に対応する `mst_page_id` が存在しない `MstPage` を検出してエラーログを出力します。

```csharp
// MasterDataRepository.cs より
var missingKomaLineIds = mstPageDatas
    .Where(page => mstKomaLineDatas.All(koma => koma.MstPageId != page.Id))
    .Select(page => page.Id)
    .ToList();
```

### ID命名規則を守る

ID は既存パターンに従って命名してください。`MstKomaLine` の `id` は `{mst_page_id}_{row番号}` のパターンが標準であるため、MstPage の ID がページ名として KomaLine ID にも埋め込まれます。後から変更すると KomaLine 側も含め全行の修正が必要になります。

### release_key の一致

`MstPage` と対応する `MstKomaLine` の `release_key` は**必ず同じ値**に揃えてください。異なると片方のみが配信されてフィールドが正常に構築されません。

### 開発・テスト用データの release_key

開発・テスト目的で追加するデータは `release_key = 999999999` を使用してください。本番配信のリリースキーを使用すると、意図せず本番環境に反映されるリスクがあります。

### MstInGame からの参照を確認する

新しい MstPage を追加した場合、`MstInGame.mst_page_id` からの参照が正しく設定されているか確認してください。MstPage は `MstInGame` から呼ばれて初めてインゲームに登場します。参照されていない MstPage はクライアントのメモリ上には存在しますが、バトル中に使用されることはありません。
