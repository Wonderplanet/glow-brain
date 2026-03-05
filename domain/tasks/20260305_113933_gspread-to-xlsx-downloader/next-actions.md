# ネクストアクション

## 次にやること

- [ ] `domain/tools/` ディレクトリの既存構成を確認する
- [ ] `domain/raw-data/google-drive/spread-sheet/` ディレクトリの有無を確認する
- [ ] サービスアカウントcredential.jsonの配置場所を決める
- [ ] Pythonスクリプト `gspread_to_xlsx.py` を `domain/tools/` に作成する
  - [ ] Google Drive API認証（サービスアカウント）
  - [ ] スプシURL/IDからファイルIDを抽出する処理
  - [ ] Google Drive APIでXLSXとしてエクスポート
  - [ ] Googleドライブ上のフォルダ構成を取得して再現して保存
  - [ ] 複数URL/IDの一括処理
- [ ] 動作確認（テスト実行）
- [ ] READMEにスクリプトの使い方を追記

## 長期的なアクション

- 既存gspread（運営設計書）の構成・内容を分析
- 運営フロー改善（AI生成による自動化）の設計

## 注意事項

- credential.jsonはGitにコミットしない（.gitignoreに追加）
- 保存先ディレクトリ: `domain/raw-data/google-drive/spread-sheet/`
- スプシのURLは `https://docs.google.com/spreadsheets/d/{ID}/...` 形式
