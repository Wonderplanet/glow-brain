# セットアップガイド

## 初回セットアップ（5分で完了）

### ステップ1: 依存関係のインストール

```bash
cd domain/tools/google/spread-sheet/spreadsheet-csv-exporter
npm install
```

### ステップ2: サービスアカウントの作成

1. [Google Cloud Console](https://console.cloud.google.com/) にアクセス
2. プロジェクトを選択（GLOWプロジェクト推奨）
3. 左メニュー > **APIとサービス** > **認証情報**
4. **認証情報を作成** > **サービスアカウント**
5. サービスアカウント名: `spreadsheet-exporter`
6. 役割: 不要（デフォルトでOK）
7. **完了** をクリック

### ステップ3: JSONキーのダウンロード

1. 作成したサービスアカウントをクリック
2. **キー** タブを開く
3. **鍵を追加** > **新しい鍵を作成**
4. 形式: **JSON**
5. **作成** をクリック（自動でダウンロード開始）

### ステップ4: credentials.json の配置

ダウンロードしたJSONファイルを `credentials/credentials.json` にリネーム・移動:

```bash
mv ~/Downloads/your-project-123456-abcdef.json credentials/credentials.json
```

### ステップ5: Google API の有効化

1. [Google Cloud Console](https://console.cloud.google.com/) で同じプロジェクトを選択
2. 左メニュー > **APIとサービス** > **ライブラリ**
3. 以下を検索して有効化:
   - **Google Sheets API** → **有効にする**
   - **Google Drive API** → **有効にする**

### ステップ6: サービスアカウントのメールアドレスをコピー

1. 左メニュー > **APIとサービス** > **認証情報**
2. 作成したサービスアカウントをクリック
3. **メール** をコピー（例: `spreadsheet-exporter@your-project.iam.gserviceaccount.com`）

### ステップ7: スプレッドシート・フォルダを共有

対象のスプレッドシート・フォルダに対して、サービスアカウントのメールアドレスを追加:

1. スプレッドシート/フォルダを開く
2. **共有** ボタンをクリック
3. コピーしたサービスアカウントのメールアドレスを貼り付け
4. 権限: **閲覧者** を選択
5. **送信**

### ステップ8: ビルド

```bash
npm run build
```

### ステップ9: 動作確認

```bash
npm run export -- single "https://docs.google.com/spreadsheets/d/YOUR_SPREADSHEET_ID/edit"
```

成功すれば `output/` ディレクトリにZIPファイルがダウンロードされます。

## よくあるエラーと対処法

### エラー: `credentials.json が見つかりません`

**原因**: credentials.json が正しく配置されていない

**対処法**:
```bash
ls -la credentials/
# credentials.json があるか確認
```

### エラー: `The caller does not have permission`

**原因**: サービスアカウントにスプレッドシート・フォルダの閲覧権限がない

**対処法**:
1. スプレッドシート/フォルダの **共有** を開く
2. サービスアカウントのメールアドレスが追加されているか確認
3. 権限が **閲覧者** 以上であるか確認

### エラー: `Google Sheets API has not been used in project before`

**原因**: Google Sheets API が有効化されていない

**対処法**:
1. [Google Cloud Console](https://console.cloud.google.com/)
2. **APIとサービス** > **ライブラリ**
3. **Google Sheets API** を検索して有効化

## セキュリティのベストプラクティス

### ✅ やるべきこと

- サービスアカウントには必要最小限の権限のみ付与
- `credentials.json` はGit管理外（既に `.gitignore` に追加済み）
- 本番環境ではサービスアカウントを専用のものにする

### ❌ やってはいけないこと

- `credentials.json` をGitにコミット
- サービスアカウントに編集権限を付与（読み取り専用でOK）
- サービスアカウントのキーを公開リポジトリに配置

## 次のステップ

1. [README.md](./README.md) で各コマンドの使い方を確認
2. `npm run export -- --help` でヘルプを表示
3. 実際のユースケースに応じてコマンドを実行

---

**困ったときは**: README.md のトラブルシューティングを参照してください
