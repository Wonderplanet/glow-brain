# チェック結果ダイアログ

「データ生成＋チェック実行」や「ダイレクトチェック」を実行すると、処理完了後にチェック結果を表示するモーダルダイアログが開きます。

---

## 画面の構成

```
┌─────────────────────────────────────────┐
│ チェック完了                          ✕ │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │ チェック完了                    │   │
│  │                                 │   │
│  │  ✅ すべてのチェックが正常に   │   │
│  │     完了しました！              │   │
│  │     （対象: 1、OK: 1、NG: 0）  │   │
│  │                                 │   │
│  │  □ エラーのみ表示  [検索ボックス]   │
│  │  [すべて展開] [折りたたみ]          │
│  │  [CSVダウンロード] [JSON] [コピー]  │
│  │                                 │   │
│  │  対象:1  OK:1  NG:0  合計:0    │   │
│  │                                 │   │
│  │  MstAutoPlayerSequence          │   │
│  │    OK  （生成: 23件、マスター: 4596件）│
│  │    全データ一致                 │   │
│  │                                 │   │
│  │  処理時間: 7.13秒               │   │
│  └─────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

---

## 各UIパーツの説明

### サマリーバー（上部の色付き帯）

全シートの照合結果を一行で表示します。

- **緑** → 全シート OK
- **赤** → 1件以上エラーあり

表示内容：`対象シート数`・`OK数`・`NG数`

---

### バッジ

サマリーバーの下に並ぶ小さなタグです。

| バッジ | 意味 |
|--------|------|
| 対象: N | チェック対象のマスターシート総数 |
| OK: N（緑） | 全データ一致したシート数 |
| NG: N（赤） | 1件以上差分があったシート数 |
| エラー合計: N | 差分の総件数 |

---

### フィルター・操作ボタン

| 操作 | 動作 |
|------|------|
| エラーのみ表示チェックボックス | NGのシートカードだけに絞り込む |
| キーワード検索ボックス | シート名またはエラー内容で絞り込む（リアルタイム） |
| すべて展開 | 全シートカードの詳細を開く |
| すべて折りたたみ | 全シートカードの詳細を閉じる |
| CSVダウンロード | 結果を `check_report_YYYY-MM-DD.csv` でダウンロード |
| JSONダウンロード | 結果を `check_report_YYYY-MM-DD.json` でダウンロード |
| テキストをコピー | 結果をプレーンテキストでクリップボードにコピー |

---

### シートカード（結果の本体）

マスターシートごとに1枚のカードが並びます。

```
MstAutoPlayerSequence        OK （生成: 23件、マスター: 4596件）
  全データ一致

MstStage                     3件のエラー （生成: 10件、マスター: 200件）
  - データ1がマスターに存在しません: [101, ...]
  - データ3がマスターに存在しません: [103, ...]
  - ユニークキー一致するが詳細データが異なります ...
```

- カードのヘッダー部分をクリックすると詳細が展開/折りたたみされます
- `生成 N件` → 設計書から抽出したレコード数
- `マスター N件` → 照合先マスターシートのレコード数

---

### 処理時間

ダイアログ下部に `処理時間: X.XX秒` として表示されます。設計書の規模やマスターシートのレコード数によって変わります。

---

## エクスポートデータの形式

### CSV

```csv
シート名,ステータス,詳細
MstAutoPlayerSequence,OK,全データ一致
MstStage,エラー,データ1がマスターに存在しません: [101, ...]
```

### JSON

```json
{
  "MstAutoPlayerSequence": {
    "message": "✅ OK",
    "errors": [],
    "generatedCount": 23,
    "masterCount": 4596
  }
}
```

### テキストコピー

```
MstAutoPlayerSequence: OK
MstStage: 3件のエラー
  - データ1がマスターに存在しません: [101, ...]
  - ...
```

---

## 実装のしくみ

### 全体の流れ

```
menuGenerateAndCheck()
    │
    ├─ convertDesignDocsToMasterFormat()   ← 設計書からデータ生成＋マスターと照合
    │       │
    │       └─ checkResults（マスター名 → {errors, generatedCount, masterCount}）を返す
    │
    └─ showCheckResultDialog(checkResults)
            │
            ├─ reportData を組み立て（CSV用のフラット形式）
            └─ createCheckResultHTML() でHTML文字列を生成
                    │
                    └─ HtmlService.createHtmlOutput().showModalDialog()
```

---

### GAS の HtmlService を使ったモーダル表示

GAS には `HtmlService` というAPIがあり、HTMLを文字列で渡すと Googleスプレッドシート上にiframeのモーダルダイアログとして表示できます。

```javascript
// コード.js: L2052-2056
const htmlOutput = HtmlService.createHtmlOutput(htmlContent)
  .setWidth(600)
  .setHeight(500);

SpreadsheetApp.getUi().showModalDialog(htmlOutput, title);
```

サイズは固定（600×500px）です。ダイアログの右上の ✕ で閉じられます。

---

### データの埋め込み方法

サーバー側（GAS）で生成した `checkResults` オブジェクトは、HTML文字列を生成する段階で `JSON.stringify()` してそのままJavaScript変数として埋め込まれます。

```javascript
// createCheckResultHTML() 内
'const rawResults = ' + JSON.stringify(checkResults) + ';'
'const reportData = ' + JSON.stringify(reportData) + ';'
```

ダイアログが開いた後のフィルタリング・検索・展開折りたたみはすべてダイアログ内のJavaScriptが処理するため、**GASサーバーへの追加通信なしに動作**します。

---

### フィルタリング・検索の仕組み

```javascript
function render() {
  const onlyErrors = document.getElementById("onlyErrors").checked;
  const q = document.getElementById("searchBox").value.trim().toLowerCase();

  Object.entries(rawResults).forEach(function(entry) {
    const name = entry[0];
    const res = entry[1];
    const errs = (res.errors || []).map(String);

    // エラーのみ表示フィルター
    if (onlyErrors && errs.length === 0) return;

    // キーワード検索（シート名＋エラー文字列全体を対象）
    const haystack = (name + " " + errs.join(" ")).toLowerCase();
    if (q && !haystack.includes(q)) return;

    // カードを描画
    container.insertAdjacentHTML("beforeend", cardHTML(name, res));
  });
}
```

チェックボックスの変更・テキスト入力のたびに `render()` が再実行され、`#results` の中身を作り直します。

---

### CSVダウンロードの仕組み

ブラウザの `Blob` API と動的に生成した `<a>` タグを使って、サーバー通信なしにファイルを保存します。

```javascript
function downloadCSV() {
  let csv = "シート名,ステータス,詳細\n";
  reportData.forEach(function(row) {
    csv += '"' + row.sheet + '","' + row.status + '","' + row.detail + '"\n';
  });

  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "check_report_2025-01-01.csv";
  link.click();  // ← 仮想クリックでダウンロード開始
}
```

JSON も同じ仕組みで、`rawResults` をそのまま `JSON.stringify(rawResults, null, 2)` して出力します。

---

### アコーディオン（展開/折りたたみ）の仕組み

カードのヘッダーをクリックすると、直後の `.details` 要素の `display` を `none` ↔ `block` で切り替えるだけのシンプルな実装です。

```javascript
function toggleDetails(headerEl) {
  const details = headerEl.nextElementSibling;
  details.style.display = details.style.display === "block" ? "none" : "block";
}
```

初期状態は `display: none`（CSS定義）で全て折りたたまれています。

---

## このダイアログが表示される機能

| メニュー | 関数 |
|---------|------|
| ⚡ データ生成＋チェック実行 | `menuGenerateAndCheck()` |
| 🔍 生成済みデータのチェックのみ | `menuCheckOnly()` |
| 🚀 ダイレクトチェック（高速） | `menuDirectCheck()` |
