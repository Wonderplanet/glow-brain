# ネクストアクション

## 完了済み

- [x] `domain/tools/` ディレクトリの既存構成を確認する
- [x] `domain/raw-data/google-drive/spread-sheet/` ディレクトリの有無を確認する（既存）
- [x] サービスアカウントcredential.jsonの配置場所を決める（スクリプトと同ディレクトリ）
- [x] Pythonスクリプト `gspread_to_xlsx.py` を `domain/tools/google-drive/gspread-to-xlsx/` に作成する
  - [x] Google Drive API認証（サービスアカウント）
  - [x] スプシURL/IDからファイルIDを抽出する処理
  - [x] Google Drive APIでXLSXとしてエクスポート
  - [x] Googleドライブ上のフォルダ構成を取得して再現して保存
  - [x] 複数URL/IDの一括処理
- [x] READMEにスクリプトの使い方を記載

## 次にやること

- [ ] 動作確認（テスト実行）
  1. `cd domain/tools/google-drive/gspread-to-xlsx`
  2. `uv sync` または `pip install -r requirements.txt`
  3. `cp credentials.json.example credentials.json` → 実際の内容を入力
  4. テスト用スプシURLで実行:
     ```bash
     uv run python gspread_to_xlsx.py \
       "https://docs.google.com/spreadsheets/d/{テストID}/edit"
     ```
  5. `domain/raw-data/google-drive/spread-sheet/` 以下にXLSXが保存されることを確認

## 長期的なアクション

- 既存gspread（運営設計書）の構成・内容を分析
- 運営フロー改善（AI生成による自動化）の設計

## 注意事項

- credential.jsonはGitにコミットしない（.gitignoreに追加済み）
- 保存先ディレクトリ: `domain/raw-data/google-drive/spread-sheet/`
- スプシのURLは `https://docs.google.com/spreadsheets/d/{ID}/...` 形式

## 実装ファイル

| ファイル | 説明 |
|---------|------|
| `domain/tools/google-drive/gspread-to-xlsx/gspread_to_xlsx.py` | メインスクリプト |
| `domain/tools/google-drive/gspread-to-xlsx/pyproject.toml` | uv依存管理 |
| `domain/tools/google-drive/gspread-to-xlsx/requirements.txt` | pip install用 |
| `domain/tools/google-drive/gspread-to-xlsx/.gitignore` | credentials.json除外 |
| `domain/tools/google-drive/gspread-to-xlsx/credentials.json.example` | サービスアカウントJSONテンプレート |
| `domain/tools/google-drive/gspread-to-xlsx/README.md` | セットアップ・使い方 |
