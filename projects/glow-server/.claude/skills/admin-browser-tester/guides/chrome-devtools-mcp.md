# chrome-devtools MCP使用ガイド

chrome-devtools MCPを使用してブラウザ操作を自動化する方法を説明します。

## 目次

- [利用可能な主要機能](#利用可能な主要機能)
- [基本操作](#基本操作)
- [Filament adminで使用される一般的なセレクタ](#filament-adminで使用される一般的なセレクタ)
- [エラー検出方法](#エラー検出方法)
- [スクリーンショット撮影](#スクリーンショット撮影)

## 利用可能な主要機能

chrome-devtools MCPツールで利用できる主要機能：

### ページ操作
- **ページ遷移**: `navigate_page` - URLを開く
- **待機**: `wait_for` - ページ読み込み、要素表示待ち
- **スナップショット**: `take_snapshot` - ページの現在状態を取得（テキストベース）
- **スクリーンショット**: `take_screenshot` - 画面キャプチャ

### 要素操作
- **要素クリック**: `click` - セレクタ指定でクリック
- **テキスト入力**: `fill` - フォーム入力
- **要素の存在確認**: スナップショットまたはセレクタで要素検索
- **ホバー**: `hover` - マウスホバー操作

### デバッグ・検証
- **コンソールログ取得**: `list_console_messages` - JavaScriptエラー確認
- **ネットワークリクエスト取得**: `list_network_requests` - API呼び出し確認

## 基本操作

### 1. ページを開く

```javascript
// URLにアクセス
navigate_page({
  url: "http://localhost:8081/admin"
})
```

### 2. 要素をクリック

```javascript
// uidを使用してクリック
click({
  uid: "element_uid_from_snapshot"
})
```

**重要**: 要素をクリックする前に、`take_snapshot`でページの現在状態を取得し、対象要素のuidを確認する必要があります。

### 3. テキストを入力

```javascript
// フォームに入力
fill({
  uid: "input_element_uid",
  value: "入力するテキスト"
})
```

### 4. 要素が表示されるまで待機

```javascript
// 特定のテキストが表示されるまで待機
wait_for({
  text: "Dashboard",
  timeout: 5000  // ミリ秒
})
```

### 5. スナップショットを取得

```javascript
// ページの現在状態を取得
take_snapshot({
  verbose: false  // 詳細情報が必要な場合はtrue
})
```

## Filament adminで使用される一般的なセレクタ

### ログインフォーム

```css
/* メールアドレス入力 */
input[type="email"]

/* パスワード入力 */
input[type="password"]

/* ログインボタン */
button[type="submit"]
```

### ナビゲーション

```css
/* サイドバーナビゲーション */
.fi-sidebar-nav

/* ナビゲーションリンク（例: Productsページへのリンク） */
.fi-sidebar-nav a[href*="/admin/products"]

/* ナビゲーションアイテム */
.fi-sidebar-item
```

### テーブル

```css
/* テーブル本体 */
.fi-table

/* テーブルヘッダーセル */
.fi-ta-header-cell

/* テーブル行 */
.fi-ta-row

/* テーブルセル */
.fi-ta-cell
```

### ボタン・アクション

```css
/* 汎用ボタン */
button.fi-btn
a.fi-btn

/* 新規作成ボタン */
button[wire\:click*="create"]

/* 編集ボタン */
button[wire\:click*="edit"]

/* 削除ボタン */
button[wire\:click*="delete"]
```

### フォーム

```css
/* フォーム本体 */
.fi-form

/* 入力フィールド */
.fi-input

/* セレクトボックス */
.fi-select

/* テキストエリア */
.fi-textarea

/* チェックボックス */
.fi-checkbox

/* ラジオボタン */
.fi-radio
```

### 通知・メッセージ

```css
/* 通知コンテナ */
.fi-notification

/* 通知タイトル */
.fi-notification-title

/* 通知本文 */
.fi-notification-body

/* 成功メッセージ */
.fi-notification.fi-color-success

/* エラーメッセージ */
.fi-notification.fi-color-danger
```

## エラー検出方法

### 1. コンソールエラーの取得

```javascript
// コンソールメッセージ一覧を取得
list_console_messages({
  types: ["error", "warn"],  // エラーと警告のみフィルタ
  pageSize: 50
})
```

### 2. ネットワークエラーの確認

```javascript
// ネットワークリクエスト一覧を取得
list_network_requests({
  resourceTypes: ["xhr", "fetch"],  // XHR/Fetchリクエストのみ
  pageSize: 50
})
```

取得したリクエストから、HTTPステータスコードが400以上のものをエラーとして検出します。

### 3. ページ上のエラーメッセージ検出

スナップショットを取得し、以下のようなキーワードを検索：
- "Error"
- "エラー"
- "失敗"
- "404 Not Found"
- "500 Internal Server Error"

## 画面サイズ設定

### 推奨画面サイズ

admin画面のテストでは、サイドバーとコンテンツが十分に表示される横幅が必要です。

```javascript
// 推奨: 1920x1080に設定
resize_page({
  width: 1920,
  height: 1080
})
```

**推奨設定**:
- 横幅: 1920px以上（サイドバーを閉じてもコンテンツが見やすい）
- 高さ: 1080px以上（フルページスクリーンショットで全体が撮れる）

**設定タイミング**: ログイン成功後、テスト実施前に設定

## スクリーンショット撮影

### 基本的な撮影方法（フルページ推奨）

**重要**: スクリーンショットは常に`fullPage: true`を使用してフルページで撮影してください。見せるべき範囲が撮影に含まれていないケースを防ぐためです。

```javascript
// フルページスクリーンショット（推奨）
take_screenshot({
  format: "png",
  fullPage: true,  // 必須: フルページで撮影
  filePath: ".claude/tmp/screenshot_20250130_123456.png"
})
```

### ビューポートのみのスクリーンショット（非推奨）

特別な理由がない限り、ビューポートのみの撮影は避けてください。

```javascript
// ビューポートのみ撮影（非推奨）
take_screenshot({
  format: "png",
  fullPage: false,  // または省略
  filePath: ".claude/tmp/viewport_screenshot.png"
})
```

### 特定要素のスクリーンショット

```javascript
// 特定要素のみを撮影
take_screenshot({
  uid: "element_uid_from_snapshot",
  format: "png",
  filePath: ".claude/tmp/element_screenshot.png"
})
```

### ファイル形式と品質

```javascript
// JPEG形式で品質指定
take_screenshot({
  format: "jpeg",
  quality: 80,  // 0-100の範囲
  filePath: ".claude/tmp/screenshot.jpg"
})
```

**推奨設定**:
- 形式: PNG形式
- フルページ: `fullPage: true`（必須）
- ファイルサイズを抑えたい場合: JPEG形式、品質80
- 保存先: `.claude/tmp/` ディレクトリ

### スクリーンショット撮影のタイミング

以下のタイミングでスクリーンショットを撮影することを推奨：

1. **ログイン成功後** - ダッシュボード画面
2. **ページ遷移後** - 対象ページの初期表示
3. **エラー発生時** - エラーメッセージが表示された画面
4. **フォーム送信後** - 成功/失敗メッセージ表示画面
5. **重要な操作完了後** - データ作成、更新、削除後の画面

### ファイル名の命名規則

スクリーンショットファイル名は以下の形式を推奨：

```
{動作内容}_{タイムスタンプ}.png
```

例:
- `login_success_20250130_123456.png`
- `products_list_20250130_123500.png`
- `error_form_validation_20250130_123510.png`
- `create_success_20250130_123520.png`

## 待機とタイミング調整

### 明示的な待機

```javascript
// 特定のテキストが表示されるまで待機
wait_for({
  text: "Dashboard",
  timeout: 10000  // 10秒
})
```

### Livewireレンダリング完了の待機

Filamentはlivewireを使用しているため、ページ遷移やアクション後に以下のような待機が必要な場合があります：

1. **ページ遷移後**: "Loading..."が消えるまで待機
2. **フォーム送信後**: 成功/エラーメッセージが表示されるまで待機
3. **テーブル更新後**: 新しいデータが表示されるまで待機

```javascript
// 例: ローディングが完了するまで待機
wait_for({
  text: "Products",  // ページタイトルなど、表示されるべきテキスト
  timeout: 5000
})
```

## トラブルシューティング

### 要素が見つからない場合

1. **ページの読み込みを待つ**
   ```javascript
   wait_for({ text: "期待されるテキスト", timeout: 5000 })
   ```

2. **スナップショットで現在の状態を確認**
   ```javascript
   take_snapshot({ verbose: true })
   ```

3. **セレクタが正しいか確認**
   - スナップショットで要素のuidを確認
   - 開発者ツールでセレクタを検証（手動確認が必要な場合）

### ブラウザ/MCP起動失敗

1. **chrome-devtools MCPの設定確認**
   - `.mcp.json`に正しく設定されているか確認

2. **Chromeブラウザの確認**
   - Chromeがインストールされているか確認

3. **MCPサーバーの再起動**
   - Claude Codeセッションを再起動

## ベストプラクティス

1. **画面サイズを設定する**: ログイン後すぐに1920x1080に設定
2. **サイドバーを閉じる**: コンテンツエリアを広く表示するため、ログイン後にサイドバーを閉じる
3. **スナップショットを活用する**: クリックや入力の前に必ずスナップショットを取得し、対象要素のuidを確認
4. **適切な待機を入れる**: ページ遷移やLivewireアクション後は必ず待機
5. **エラーを早期検出**: 各操作後にコンソールログを確認
6. **フルページで証拠を残す**: 重要な画面や問題発生時は必ず`fullPage: true`でスクリーンショット
7. **段階的に進める**: 一度に複数の操作をせず、1つずつ確認しながら進める
