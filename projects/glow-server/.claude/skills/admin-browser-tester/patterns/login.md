# 標準ログイン手順

すべてのadmin動作確認テストで使用する、標準的なログイン手順を説明します。

## 概要

admin画面にアクセスするための固定フローです。すべてのテストケースで最初に実行します。

## 前提条件

- Docker環境が起動している
- NGINX_ADMIN_PORTが確認済み（デフォルト: 8081）
- 認証情報が確認済み（デフォルト: admin@wonderpla.net / admin）

参照: **[環境セットアップガイド](../guides/environment-setup.md)**

## ログイン手順

### ステップ1: admin URLにアクセス

```javascript
navigate_page({
  url: "http://localhost:{NGINX_ADMIN_PORT}/admin"
})
```

**例**:
```javascript
navigate_page({
  url: "http://localhost:8081/admin"
})
```

### ステップ2: ログインページの表示を確認

スナップショットを取得して、ログインフォームが表示されていることを確認します。

```javascript
take_snapshot()
```

**確認項目**:
- メールアドレス入力フィールドが存在する
- パスワード入力フィールドが存在する
- ログインボタンが存在する

### ステップ3: メールアドレスを入力

```javascript
fill({
  uid: "email_input_uid",  // スナップショットから取得したuid
  value: "admin@wonderpla.net"
})
```

**デフォルト値**: `admin@wonderpla.net`

### ステップ4: パスワードを入力

```javascript
fill({
  uid: "password_input_uid",  // スナップショットから取得したuid
  value: "admin"
})
```

**デフォルト値**: `admin`

### ステップ5: ログインボタンをクリック

```javascript
click({
  uid: "login_button_uid"  // スナップショットから取得したuid
})
```

### ステップ6: ログイン成功を確認

ダッシュボードページが表示されるまで待機します。

```javascript
wait_for({
  text: "Dashboard",
  timeout: 5000
})
```

または

```javascript
wait_for({
  text: "ダッシュボード",
  timeout: 5000
})
```

### ステップ7: 画面横幅を設定

テスト実行のため、ブラウザの横幅を広めに設定します。

```javascript
// 画面サイズを1920x1080に設定（推奨）
resize_page({
  width: 1920,
  height: 1080
})
```

**推奨サイズ**: 横幅1920px以上（サイドバーとコンテンツが十分に表示される）

### ステップ8: サイドバーを閉じる

コンテンツエリアを広く表示するため、左サイドバーを閉じます。

```javascript
// スナップショットでサイドバーの状態を確認
take_snapshot()

// サイドバーのトグルボタンをクリック
click({
  uid: "sidebar_toggle_button_uid"  // スナップショットから取得
})

// サイドバーが閉じるまで待機
wait_for({
  text: "コンテンツが見える状態",  // または特定の要素
  timeout: 2000
})
```

**セレクタの例**:
- サイドバートグルボタン: `.fi-sidebar-toggle-button`, `[data-sidebar-toggle]`
- ハンバーガーメニューアイコン

**注意**: Filamentのバージョンによってセレクタが異なる場合があるため、スナップショットで確認してください。

### ステップ9: ダッシュボード表示の検証

スナップショットまたはスクリーンショットを取得して、正常にログインできたことを確認します。

```javascript
// スナップショット取得
take_snapshot()

// フルページスクリーンショット撮影（推奨）
take_screenshot({
  format: "png",
  fullPage: true,  // フルページで撮影
  filePath: ".claude/tmp/login_success.png"
})
```

**確認項目**:
- ページタイトルに「Dashboard」または「ダッシュボード」が含まれる
- エラーメッセージが表示されていない
- ナビゲーションメニューが表示されている
- ユーザー名が表示されている（右上など）
- サイドバーが閉じられている

## ログイン失敗時の対処

### パターン1: 認証情報が間違っている

エラーメッセージ例:
- "These credentials do not match our records."
- "認証情報が一致しません"

**対処方法**:
1. database/seeders/AdminUserSeeder.phpを確認
2. .envファイルのADMIN_EMAIL、ADMIN_PASSWORDを確認
3. artisan tinkerで管理者ユーザーを検索

詳細: **[環境セットアップガイド - 認証情報の確認](../guides/environment-setup.md#認証情報の確認)**

### パターン2: ユーザーが存在しない

エラーメッセージ例:
- "These credentials do not match our records."

**対処方法**:
```bash
# シーダーを実行して管理者ユーザーを作成
./tools/bin/sail-wp admin artisan db:seed --class=AdminUserSeeder
```

### パターン3: ページが表示されない（404エラー）

**原因**:
- Dockerコンテナが起動していない
- NGINX_ADMIN_PORTが間違っている
- ルーティング設定が誤っている

**対処方法**:
```bash
# Docker環境の起動状態を確認
docker compose ps

# 起動していない場合は起動
./tools/bin/sail-wp up -d

# ポート番号を再確認
grep NGINX_ADMIN_PORT .env
```

### パターン4: 500エラーが発生

**原因**:
- データベース接続エラー
- アプリケーションエラー

**対処方法**:
```bash
# Laravelログを確認
./tools/bin/sail-wp admin logs

# またはコンテナ内のログファイルを確認
docker compose exec php cat storage/logs/laravel.log
```

## 完全なログインフロー例

以下は、実際のテスト実装例です：

```javascript
// 1. admin URLにアクセス
navigate_page({
  url: "http://localhost:8081/admin"
})

// 2. ログインページが表示されるまで待機
wait_for({
  text: "Sign in",
  timeout: 5000
})

// 3. ページの現在状態を取得
const snapshot = take_snapshot()

// 4. メールアドレスを入力
fill({
  uid: "email_input_uid",  // スナップショットから取得
  value: "admin@wonderpla.net"
})

// 5. パスワードを入力
fill({
  uid: "password_input_uid",  // スナップショットから取得
  value: "admin"
})

// 6. ログインボタンをクリック
click({
  uid: "login_button_uid"  // スナップショットから取得
})

// 7. ダッシュボードが表示されるまで待機
wait_for({
  text: "Dashboard",
  timeout: 5000
})

// 8. 画面サイズを設定
resize_page({
  width: 1920,
  height: 1080
})

// 9. サイドバーを閉じる
take_snapshot()  // サイドバートグルボタンのuidを取得
click({
  uid: "sidebar_toggle_button_uid"
})
wait_for({
  text: "コンテンツが見える状態",
  timeout: 2000
})

// 10. ログイン成功のフルページスクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,  // フルページで撮影
  filePath: ".claude/tmp/login_success.png"
})

// 11. コンソールエラーを確認
const consoleMessages = list_console_messages({
  types: ["error"]
})
```

## ログイン状態の維持

chrome-devtools MCPでは、セッションが維持されるため、一度ログインすれば同じブラウザセッション内では再ログインは不要です。

**ただし、以下の場合は再ログインが必要**:
- ブラウザを閉じて再度開いた場合
- セッションタイムアウトが発生した場合
- ログアウト操作を実行した場合

## チェックリスト

ログイン手順が正常に完了したことを確認：

- [ ] admin URLにアクセスできた
- [ ] ログインフォームが表示された
- [ ] メールアドレスを入力できた
- [ ] パスワードを入力できた
- [ ] ログインボタンをクリックできた
- [ ] ダッシュボードが表示された
- [ ] エラーメッセージが表示されていない
- [ ] ナビゲーションメニューが表示されている
- [ ] コンソールエラーが発生していない
