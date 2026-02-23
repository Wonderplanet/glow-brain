# ネクストアクション

## 次にやること

### Step 1: XLSX→CSV/JSON変換スクリプトの作成
- [ ] openpyxlを使ったPythonスクリプトを作成
  - 実データ → CSV（シートごと）
  - 式・書式・セル情報 → JSON
- [ ] `scripts/xlsx_to_csv_json.py` として実装
- [ ] 動作確認（既存の設計書XLSXで試す）

### Step 2: CLIツールとして整備
- [ ] コマンド一発で変換できるCLIインターフェースを追加
- [ ] `python scripts/xlsx_to_csv_json.py <path/to/file.xlsx>` で動作するように
- [ ] 出力先フォルダの自動生成

### Step 3: Claude Codeスキルとして組み込む
- [ ] `.claude/skills/spreadsheet-reader/` スキルを作成
- [ ] スキルからXLSX→CSV/JSONの変換を呼び出せるようにする
- [ ] 変換されたCSV/JSONをClaude Codeが分析してマスタデータ生成式を提案

### Step 4: インゲーム設計書テンプレートの作成
- [ ] 変換スクリプト・スキルを活用して、設計書スプシのテンプレートを分析
- [ ] マスタデータ生成式を追加したスプシテンプレートをAIで作成

## 長期的なアクション

- インゲーム設計書 → マスタデータ生成式追加 → CSV出力の完全自動化
- 複数の設計書タイプ（インゲーム、イベント等）に対応したテンプレートの整備
- Claude Codeでの設計書レビュー・改善提案機能

## 注意事項

- XLSXの式情報（セル参照、VLOOKUP等）はopenpyxlで`data_only=False`で読み込むと取得できる
- 実データ（計算結果）は`data_only=True`で読み込む必要がある
- Googleスプレッドシート固有の関数（GOOGLEFINANCE等）はopenpyxlでは解釈できないことに注意
