# トラブルシューティング

admin動作確認テスト中によく発生する問題とその解決方法をまとめています。

## 目次

- [ブラウザ/MCP関連の問題](#ブラウザmcp関連の問題)
- [ログイン関連の問題](#ログイン関連の問題)
- [ページ表示関連の問題](#ページ表示関連の問題)
- [要素操作関連の問題](#要素操作関連の問題)
- [スクリーンショット関連の問題](#スクリーンショット関連の問題)
- [パフォーマンス関連の問題](#パフォーマンス関連の問題)

## ブラウザ/MCP関連の問題

### 問題1: chrome-devtools MCPが起動しない

**症状**:
- ブラウザ操作ツールが応答しない
- "MCP server not found" 等のエラーメッセージ

**原因**:
- chrome-devtools MCPが.mcp.jsonに正しく設定されていない
- MCPサーバーが起動していない
- Chromeブラウザがインストールされていない

**解決方法**:

1. `.mcp.json` の設定を確認
   ```json
   {
     "mcpServers": {
       "chrome-devtools": {
         "command": "npx",
         "args": ["-y", "@wonderpla/mcp-server-chrome-devtools"]
       }
     }
   }
   ```

2. Chromeブラウザのインストール確認
   ```bash
   # macOSの場合
   ls "/Applications/Google Chrome.app"

   # インストールされていない場合は、Google Chromeをインストール
   ```

3. Claude Codeセッションを再起動

### 問題2: ブラウザウィンドウが表示されない

**症状**:
- MCPは動作しているが、ブラウザウィンドウが見えない

**原因**:
- ヘッドレスモードで動作している（正常動作）

**解決方法**:
- chrome-devtools MCPはヘッドレスモードで動作するため、ブラウザウィンドウは表示されません
- スナップショットやスクリーンショットで画面の状態を確認してください

## ログイン関連の問題

### 問題3: ログインできない（認証情報エラー）

**症状**:
- "These credentials do not match our records" エラー
- ログインフォーム送信後、ダッシュボードに遷移しない

**原因**:
- 認証情報（メールアドレス/パスワード）が間違っている
- 管理者ユーザーが存在しない
- データベース接続エラー

**解決方法**:

1. デフォルト認証情報を確認
   ```bash
   # AdminUserSeeder.phpを確認
   cat admin/database/seeders/AdminUserSeeder.php
   ```

2. 管理者ユーザーの存在確認
   ```bash
   ./tools/bin/sail-wp admin artisan tinker
   ```
   ```php
   \App\Models\AdminUser::where('email', 'admin@wonderpla.net')->first();
   ```

3. 管理者ユーザーが存在しない場合、作成
   ```bash
   ./tools/bin/sail-wp admin artisan db:seed --class=AdminUserSeeder
   ```

### 問題4: ログインページにリダイレクトされ続ける

**症状**:
- ログイン成功後、再びログインページに戻される

**原因**:
- セッション設定の問題
- Cookieが保存されていない

**解決方法**:

1. セッション設定を確認
   ```bash
   # .envファイルでセッション設定を確認
   grep SESSION_ admin/.env
   ```

2. セッションをクリア
   ```bash
   ./tools/bin/sail-wp admin artisan cache:clear
   ./tools/bin/sail-wp admin artisan session:clear
   ```

3. 再度ログインを試行

## ページ表示関連の問題

### 問題5: ページが表示されない（404エラー）

**症状**:
- "404 Not Found" エラー
- "Page not found" 画面が表示される

**原因**:
- ルーティングが正しく登録されていない
- Filamentのキャッシュが古い
- URLが間違っている

**解決方法**:

1. Filamentキャッシュをクリア
   ```bash
   ./tools/bin/sail-wp admin artisan filament:clear-cache
   ```

2. アプリケーションキャッシュをクリア
   ```bash
   ./tools/bin/sail-wp admin artisan cache:clear
   ./tools/bin/sail-wp admin artisan config:clear
   ./tools/bin/sail-wp admin artisan route:clear
   ```

3. URLが正しいか確認
   - 正: `http://localhost:8081/admin/products`
   - 誤: `http://localhost:8081/products`（`/admin`が抜けている）

### 問題6: ページが表示されない（500エラー）

**症状**:
- "500 Internal Server Error" エラー
- "Whoops, something went wrong" 画面が表示される

**原因**:
- PHPの構文エラー
- データベース接続エラー
- 未定義のメソッド/プロパティ呼び出し

**解決方法**:

1. Laravelログを確認
   ```bash
   ./tools/bin/sail-wp admin artisan tail
   ```
   または
   ```bash
   docker compose exec php cat admin/storage/logs/laravel.log | tail -50
   ```

2. デバッグモードを有効化（開発環境のみ）
   ```bash
   # .envファイルで確認
   grep APP_DEBUG admin/.env
   # APP_DEBUG=true になっていることを確認
   ```

3. エラーメッセージから原因を特定し、修正

### 問題7: ページの読み込みが遅い

**症状**:
- ページ表示に10秒以上かかる
- `wait_for` がタイムアウトする

**原因**:
- N+1問題（データベースクエリの最適化不足）
- 大量のデータを読み込んでいる
- 外部API呼び出しが遅い

**解決方法**:

1. データベースクエリを確認
   ```bash
   # Laravel Debugbarで確認（開発環境）
   # またはクエリログを有効化
   ```

2. Eager Loadingを設定
   ```php
   public static function getEloquentQuery(): Builder
   {
       return parent::getEloquentQuery()->with(['category', 'tags']);
   }
   ```

3. ページネーションを確認
   ```php
   // 1ページあたりの件数を減らす
   protected static int $defaultPerPage = 25;
   ```

## 要素操作関連の問題

### 問題8: 要素が見つからない（Element not found）

**症状**:
- `click()` や `fill()` で "Element not found" エラー
- uidが存在しないエラー

**原因**:
- ページの読み込みが完了していない
- Livewireのレンダリングが完了していない
- セレクタ/uidが間違っている
- 要素が動的に生成される

**解決方法**:

1. ページ読み込み完了を待機
   ```javascript
   wait_for({
     text: "期待されるテキスト",
     timeout: 5000
   })
   ```

2. スナップショットで現在の状態を確認
   ```javascript
   take_snapshot({ verbose: true })
   ```

3. 正しいuidを使用
   - スナップショットから取得したuidを使用
   - 古いスナップショットのuidは使用しない

4. Livewire処理完了を待つ
   ```javascript
   // "Loading..." が消えるまで待機
   wait_for({
     text: "Products",
     timeout: 5000
   })
   ```

### 問題9: ボタンをクリックしても反応しない

**症状**:
- `click()` を実行してもページ遷移しない
- アクションが実行されない

**原因**:
- JavaScriptエラーが発生している
- Livewireの処理が失敗している
- ボタンが無効化されている

**解決方法**:

1. コンソールエラーを確認
   ```javascript
   list_console_messages({
     types: ["error"],
     pageSize: 50
   })
   ```

2. ボタンの状態を確認
   ```javascript
   // スナップショットでボタンが有効か確認
   take_snapshot()
   ```

3. クリック後に待機を追加
   ```javascript
   click({ uid: "button_uid" })

   // ページ遷移やLivewire処理完了を待つ
   wait_for({
     text: "期待される結果",
     timeout: 5000
   })
   ```

### 問題10: フォーム入力ができない

**症状**:
- `fill()` でテキスト入力ができない
- 入力した値が保存されない

**原因**:
- フィールドが読み取り専用になっている
- フィールドのuidが間違っている
- Livewireのバインディングエラー

**解決方法**:

1. スナップショットでフィールドを確認
   ```javascript
   take_snapshot({ verbose: true })
   ```

2. 正しいuidを使用

3. 入力後にフォーカスを移動
   ```javascript
   fill({ uid: "field_uid", value: "test" })

   // 次のフィールドをクリックして、フォーカスを移動
   click({ uid: "next_field_uid" })
   ```

## スクリーンショット関連の問題

### 問題11: スクリーンショットが保存されない

**症状**:
- `take_screenshot()` を実行してもファイルが作成されない
- "Permission denied" エラー

**原因**:
- 保存先ディレクトリが存在しない
- ディレクトリの書き込み権限がない

**解決方法**:

1. 保存先ディレクトリを作成
   ```bash
   mkdir -p .claude/tmp
   ```

2. 書き込み権限を確認
   ```bash
   ls -la .claude/tmp
   ```

3. 権限がない場合は付与
   ```bash
   chmod 755 .claude/tmp
   ```

### 問題12: スクリーンショットが真っ白

**症状**:
- スクリーンショットが保存されるが、画面が真っ白

**原因**:
- ページの読み込みが完了していない
- CSSがロードされていない

**解決方法**:

1. ページ読み込み完了を待機
   ```javascript
   wait_for({
     text: "ページのメインコンテンツ",
     timeout: 10000
   })
   ```

2. 追加の待機時間を設ける
   ```javascript
   // JavaScriptの待機（evaluate_scriptを使用）
   evaluate_script({
     function: "() => new Promise(resolve => setTimeout(resolve, 2000))"
   })
   ```

## パフォーマンス関連の問題

### 問題13: N+1問題が検出される

**症状**:
- ネットワークリクエストが大量に発生
- 同じURLへのリクエストが繰り返される

**原因**:
- Eloquentリレーションの事前読み込みが設定されていない

**解決方法**:

1. Eager Loadingを設定
   ```php
   public static function getEloquentQuery(): Builder
   {
       return parent::getEloquentQuery()->with([
           'category',
           'tags',
           'author'
       ]);
   }
   ```

2. テーブルカラムでのリレーション使用時
   ```php
   Tables\Columns\TextColumn::make('category.name')
       ->label('Category'),
   ```
   上記の場合、`category` リレーションを事前読み込み

### 問題14: メモリ不足エラー

**症状**:
- "Allowed memory size exhausted" エラー

**原因**:
- 大量のデータを一度に読み込んでいる
- メモリリークが発生している

**解決方法**:

1. ページネーションを確認
   ```php
   protected static int $defaultPerPage = 25; // デフォルト値を減らす
   ```

2. クエリを最適化
   ```php
   // 必要なカラムのみ取得
   public static function getEloquentQuery(): Builder
   {
       return parent::getEloquentQuery()
           ->select(['id', 'name', 'email', 'created_at']);
   }
   ```

## 緊急時の対処

### すべてのキャッシュをクリア

```bash
# Laravelキャッシュをすべてクリア
./tools/bin/sail-wp admin artisan cache:clear
./tools/bin/sail-wp admin artisan config:clear
./tools/bin/sail-wp admin artisan route:clear
./tools/bin/sail-wp admin artisan view:clear
./tools/bin/sail-wp admin artisan filament:clear-cache

# Composerの最適化を再実行
./tools/bin/sail-wp admin composer dump-autoload
```

### Dockerコンテナを再起動

```bash
# コンテナを停止
docker compose down

# コンテナを起動
./tools/bin/sail-wp up -d

# 起動完了を待つ
sleep 30
```

### データベースをリセット

```bash
# 注意: すべてのデータが削除されます
./tools/bin/sail-wp admin artisan migrate:fresh --seed
```

## サポート情報

問題が解決しない場合：

1. Laravelログを確認: `admin/storage/logs/laravel.log`
2. Docker logsを確認: `docker compose logs -f php`
3. GitHubイシューを確認: glow-serverリポジトリのイシュー
4. チーム内で相談
