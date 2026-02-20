# Spreadsheet CSV Exporter (Local Script)

Google Spreadsheetをローカルスクリプトで一括CSVエクスポート（サービスアカウント認証）

## 📋 概要

GASのWebアプリ版 `domain/tools/masterdata-entry/spreadsheet-csv-exporter` をローカルスクリプトとして新規実装したツールです。

### 主な機能

1. **単一スプレッドシートダウンロード** - URLからスプシ全シートをCSV→ZIP化
2. **フォルダ一括ダウンロード** - フォルダ内のスプシ一覧表示→選択ダウンロード
3. **シート名フィルタ** - 指定シート名のみ抽出
4. **複数URL一括** - 複数スプシをまとめてダウンロード
5. **進捗管理表スキャン** - 「進捗管理表」シートからURL抽出

### GAS版との違い

| 項目 | GAS版 | Local版 |
|------|-------|---------|
| 実行環境 | Webアプリ（ブラウザ） | ローカルスクリプト（CLI） |
| 認証方式 | ユーザーOAuth | サービスアカウント |
| 実装言語 | JavaScript | TypeScript (Node.js) |
| UI | Webインターフェース | コマンドライン |
| 実行時間制限 | 6分 | なし |

## 🚀 セットアップ

### 1. 依存関係のインストール

```bash
cd domain/tools/google/spread-sheet/spreadsheet-csv-exporter
npm install
```

### 2. サービスアカウントの作成とキー取得

Google Cloud Consoleでサービスアカウントを作成し、JSONキーをダウンロードします。

#### 手順

1. [Google Cloud Console](https://console.cloud.google.com/) にアクセス
2. プロジェクトを選択（または新規作成）
3. **APIとサービス** > **認証情報** へ移動
4. **認証情報を作成** > **サービスアカウント** を選択
5. サービスアカウント名を入力（例: `spreadsheet-exporter`）して作成
6. 作成したサービスアカウントをクリック
7. **キー** タブ > **鍵を追加** > **新しい鍵を作成** > **JSON** を選択
8. ダウンロードされた `credentials.json` を `credentials/` ディレクトリに配置

```bash
mv ~/Downloads/your-project-123456-abcdef123456.json credentials/credentials.json
```

### 3. Google Sheets API / Google Drive API の有効化

1. [Google Cloud Console](https://console.cloud.google.com/) でプロジェクトを選択
2. **APIとサービス** > **ライブラリ** へ移動
3. 以下のAPIを検索して有効化:
   - **Google Sheets API**
   - **Google Drive API**

### 4. スプレッドシート・フォルダの共有設定

サービスアカウントのメールアドレス（例: `spreadsheet-exporter@your-project.iam.gserviceaccount.com`）に対して、対象のスプレッドシート・フォルダへの**閲覧権限**を付与してください。

#### 共有方法

- スプレッドシート/フォルダを開く
- **共有** ボタンをクリック
- サービスアカウントのメールアドレスを追加
- 権限を **閲覧者** に設定
- **送信**

### 5. ビルド

```bash
npm run build
```

## 📖 使い方

### 基本コマンド

```bash
npm run export -- <command> [options]
```

### コマンド一覧

#### 1. 単一スプレッドシートダウンロード

```bash
npm run export -- single "https://docs.google.com/spreadsheets/d/ABC123..."
```

- 指定したスプレッドシートの全シートをCSVに変換
- ZIPファイルとしてダウンロード

#### 2. フォルダ一括ダウンロード

```bash
npm run export -- folder "https://drive.google.com/drive/folders/XYZ789..."
```

- フォルダ内のスプレッドシート一覧を表示
- インタラクティブに選択してダウンロード

実行例:
```
=== スプレッドシート一覧 ===
[1] マスタデータ設計書A
[2] マスタデータ設計書B
[3] マスタデータ設計書C

ダウンロードする番号をカンマ区切りで入力（全て: all）: 1,3
```

#### 3. シート名フィルタダウンロード

```bash
npm run export -- filter "https://drive.google.com/drive/folders/XYZ789..." "MstEvent"
```

- フォルダ内の全スプレッドシートから「MstEvent」シートのみを抽出
- 1つのZIPファイルにまとめてダウンロード

#### 4. 複数スプレッドシート一括ダウンロード

```bash
npm run export -- multiple urls.txt
```

`urls.txt` に改行区切りでURLを記載:
```
https://docs.google.com/spreadsheets/d/ABC123...
https://docs.google.com/spreadsheets/d/DEF456...
https://docs.google.com/spreadsheets/d/GHI789...
```

- 複数のスプレッドシートをまとめて1つのZIPファイルにダウンロード

#### 5. 進捗管理表スキャンダウンロード

```bash
npm run export -- scan "https://drive.google.com/drive/folders/XYZ789..."
```

- フォルダ内のスプレッドシートから「進捗管理表」シートを検索
- シート内に記載されたスプレッドシートURLを抽出
- インタラクティブに選択してダウンロード

### オプション

| オプション | 説明 | デフォルト |
|-----------|------|-----------|
| `-o, --output-dir <dir>` | 出力ディレクトリ | `./output` |
| `-v, --verbose` | 詳細ログを表示 | `false` |

#### 例: 出力先を変更

```bash
npm run export -- single "https://docs.google.com/spreadsheets/d/ABC123..." -o /path/to/output
```

#### 例: 詳細ログを表示

```bash
npm run export -- single "https://docs.google.com/spreadsheets/d/ABC123..." -v
```

## 🛠️ ファイル構成

```
spreadsheet-csv-exporter/
├── src/
│   ├── index.ts              # CLIエントリーポイント
│   ├── auth.ts               # サービスアカウント認証
│   ├── exporter.ts           # CSV/ZIP生成ロジック
│   ├── commands/             # 各コマンド実装
│   │   ├── single.ts         # 単一スプシダウンロード
│   │   ├── folder.ts         # フォルダ一括ダウンロード
│   │   ├── filter.ts         # シート名フィルタ
│   │   ├── multiple.ts       # 複数URL一括
│   │   └── scan.ts           # 進捗管理表スキャン
│   ├── utils.ts              # ユーティリティ関数
│   └── types.ts              # 型定義
├── credentials/              # サービスアカウントキー配置
│   └── credentials.json      # ★ここに配置（git管理外）
├── output/                   # ダウンロード先（デフォルト）
├── dist/                     # ビルド成果物（自動生成）
├── package.json
├── tsconfig.json
├── .gitignore
└── README.md
```

## 📝 注意事項

### セキュリティ

- ✅ **参照のみ**: スプレッドシートの編集は一切行いません（readonly権限）
- ✅ **サービスアカウント**: 共有されたファイルのみアクセス可能
- ⚠️ **credentials.json**: Git管理外（`.gitignore`に追加済み）

### パフォーマンス

- **レートリミット対策**: 各シートエクスポート後に500msスリープ
- **リトライ処理**: エラー時は最大3回リトライ（3秒/6秒/9秒待機）
- **CSV文字コード**: UTF-8（BOM付き）でエクスポート

### GAS版との機能差異

| 機能 | GAS版 | Local版 |
|------|-------|---------|
| リアルタイムログ表示 | ✅ WebUI | ⚠️ コンソールログ |
| 中断機能 | ✅ | ❌ (Ctrl+C) |
| インタラクティブ選択 | ✅ チェックボックス | ✅ 番号入力 |
| 実行時間制限 | ⚠️ 6分 | ✅ なし |

## 🐛 トラブルシューティング

### `credentials.json が見つかりません`

- `credentials/credentials.json` が正しく配置されているか確認
- ファイル名が正確に `credentials.json` であることを確認

### `権限がありません` エラー

- サービスアカウントのメールアドレスにスプレッドシート・フォルダの閲覧権限が付与されているか確認
- Google Sheets API / Google Drive API が有効化されているか確認

### レートリミットエラー（HTTP 429）

- リトライ処理が自動で実行されます
- 連続エラーの場合は `exporter.ts` の `sleep()` 値を増やしてください

### ZIP内のCSVが文字化けする

- UTF-8（BOM付き）で出力しています
- Excelで開く場合、インポート時に「UTF-8」を指定してください

## 📚 参考

- [Google Sheets API](https://developers.google.com/sheets/api)
- [Google Drive API](https://developers.google.com/drive/api)
- [サービスアカウント認証](https://cloud.google.com/iam/docs/service-accounts)

## 🔗 関連ツール

- `domain/tools/masterdata-entry/spreadsheet-csv-exporter/` - GAS版（Webアプリ）

---

**作成者**: GLOW Brain Project
**最終更新**: 2026-02-10
