# フォーム/バリデーション変更のテスト

フォームのバリデーションルールを追加・変更した際のテスト手順を説明します。

## 対象となる実装

以下のような実装後にこのテストパターンを使用します：

- バリデーションルールの追加・変更
- フォームフィールドの追加・削除
- カスタムバリデーションルールの実装
- フォームレイアウトの変更

## テストの目的

バリデーション機能が以下を満たすことを確認：

1. 正常値での送信が成功する
2. 異常値で適切なエラーメッセージが表示される
3. 必須項目のチェックが機能する
4. データフォーマットの検証が機能する
5. エラーメッセージが適切な言語で表示される

## 事前準備

1. **実装内容の確認**
   - 変更したバリデーションルールを確認
   - 対象フィールドを特定
   - 期待される動作を確認

2. **ログインとページ遷移**
   - 標準ログイン手順を実行
   - 対象フォームのページへ遷移
   - 参照: **[標準ログイン手順](login.md)**

## テスト手順

### フェーズ1: 正常値でのテスト

#### ステップ1.1: フォームを開く

```javascript
// 新規作成または編集フォームを開く
click({
  uid: "create_or_edit_button_uid"
})

// フォーム表示を待機
wait_for({
  text: "Create",  // または "Edit"
  timeout: 5000
})
```

#### ステップ1.2: 全フィールドに正常値を入力

すべてのフィールドに、バリデーションルールを満たす正常な値を入力します。

```javascript
// 例: 名前フィールド（必須、最大255文字）
fill({
  uid: "name_field_uid",
  value: "Valid Product Name"
})

// 例: メールアドレス（必須、メール形式）
fill({
  uid: "email_field_uid",
  value: "test@example.com"
})

// 例: 価格（必須、数値、最小0）
fill({
  uid: "price_field_uid",
  value: "1000"
})

// 例: URLフィールド（URL形式）
fill({
  uid: "url_field_uid",
  value: "https://example.com"
})
```

#### ステップ1.3: 保存ボタンをクリック

```javascript
click({
  uid: "save_button_uid"
})
```

#### ステップ1.4: 成功メッセージの確認

```javascript
// 成功メッセージが表示されるまで待機
wait_for({
  text: "successfully",
  timeout: 5000
})

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_success.png"
})
```

**確認項目**:
- ✅ 成功メッセージが表示される
- ✅ データが保存される
- ✅ エラーメッセージが表示されない

### フェーズ2: 必須項目のバリデーションテスト

#### ステップ2.1: フォームを開く（新規）

```javascript
click({
  uid: "create_button_uid"
})

wait_for({
  text: "Create",
  timeout: 5000
})
```

#### ステップ2.2: 必須項目を空で送信

何も入力せずに保存ボタンをクリックします。

```javascript
// 何も入力せずに保存
click({
  uid: "save_button_uid"
})
```

#### ステップ2.3: バリデーションエラーの確認

```javascript
// エラーメッセージが表示されるまで待機
wait_for({
  text: "required",  // または「必須」「入力してください」
  timeout: 3000
})

// スナップショット取得
take_snapshot({ verbose: true })

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_required_error.png"
})
```

**確認項目**:
- ❌ 各必須項目にエラーメッセージが表示される
- ❌ エラーメッセージが適切な言語（日本語など）で表示される
- ❌ エラーメッセージがフィールドの近くに表示される
- ✅ フォームが送信されていない（ページ遷移していない）
- ✅ 入力済みの値が保持されている

### フェーズ3: データフォーマットのバリデーションテスト

各フィールドタイプに応じて、不正な形式の値を入力してテストします。

#### テストケース3.1: メールアドレス形式

```javascript
// フォームを開く
click({ uid: "create_button_uid" })
wait_for({ text: "Create", timeout: 5000 })

// 不正なメールアドレスを入力
fill({
  uid: "email_field_uid",
  value: "invalid-email"  // @がない
})

// 他の必須項目は正常値を入力
fill({
  uid: "name_field_uid",
  value: "Test Name"
})

// 保存を試行
click({ uid: "save_button_uid" })

// エラーメッセージ確認
wait_for({
  text: "valid email",  // または「正しいメールアドレス」
  timeout: 3000
})

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_email_error.png"
})
```

**確認項目**:
- ❌ メールアドレスフィールドにエラーメッセージが表示される
- ❌ "valid email address"または「正しいメールアドレスを入力してください」等のメッセージ

#### テストケース3.2: URL形式

```javascript
// URLフィールドに不正な値を入力
fill({
  uid: "url_field_uid",
  value: "not-a-url"  // URL形式ではない
})

click({ uid: "save_button_uid" })

wait_for({
  text: "valid URL",  // または「正しいURL」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_url_error.png"
})
```

#### テストケース3.3: 数値形式

```javascript
// 数値フィールドに文字列を入力
fill({
  uid: "price_field_uid",
  value: "abc"  // 数値ではない
})

click({ uid: "save_button_uid" })

wait_for({
  text: "must be a number",  // または「数値を入力してください」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_number_error.png"
})
```

#### テストケース3.4: 日付形式

```javascript
// 日付フィールドに不正な形式を入力
fill({
  uid: "date_field_uid",
  value: "2025/99/99"  // 不正な日付
})

click({ uid: "save_button_uid" })

wait_for({
  text: "valid date",  // または「正しい日付」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_date_error.png"
})
```

### フェーズ4: 範囲・長さのバリデーションテスト

#### テストケース4.1: 最大長超過

```javascript
// 最大長を超える文字列を入力（例: 最大255文字の場合）
fill({
  uid: "name_field_uid",
  value: "a".repeat(256)  // 256文字
})

click({ uid: "save_button_uid" })

wait_for({
  text: "may not be greater than",  // または「255文字以下」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_max_length_error.png"
})
```

#### テストケース4.2: 最小値未満

```javascript
// 最小値未満の数値を入力（例: 最小0の場合）
fill({
  uid: "price_field_uid",
  value: "-100"
})

click({ uid: "save_button_uid" })

wait_for({
  text: "must be at least",  // または「0以上」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_min_value_error.png"
})
```

#### テストケース4.3: 最大値超過

```javascript
// 最大値を超える数値を入力（例: 最大10000の場合）
fill({
  uid: "price_field_uid",
  value: "100000"
})

click({ uid: "save_button_uid" })

wait_for({
  text: "may not be greater than",  // または「10000以下」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_max_value_error.png"
})
```

### フェーズ5: カスタムバリデーションルールのテスト

カスタムバリデーションルールを実装した場合、そのルールに応じたテストを実施します。

#### 例: ユニーク制約のテスト

```javascript
// 既存のデータと同じ値を入力
fill({
  uid: "email_field_uid",
  value: "admin@wonderpla.net"  // 既に存在するメールアドレス
})

click({ uid: "save_button_uid" })

wait_for({
  text: "already been taken",  // または「既に使用されています」
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_unique_error.png"
})
```

#### 例: 条件付きバリデーションのテスト

```javascript
// 特定の条件でのみ必須になるフィールドのテスト
// 例: type="premium"の場合、price_premiumが必須

// typeを"premium"に設定
fill({
  uid: "type_field_uid",
  value: "premium"
})

// price_premiumを空のままにして保存
click({ uid: "save_button_uid" })

// エラーメッセージ確認
wait_for({
  text: "required when type is premium",
  timeout: 3000
})

take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_conditional_error.png"
})
```

## エラーメッセージの多言語対応確認

### 日本語環境での確認

バリデーションエラーメッセージが日本語で表示されることを確認します。

**確認項目**:
- "required" → 「必須です」「入力してください」
- "valid email" → 「正しいメールアドレスを入力してください」
- "must be a number" → 「数値を入力してください」
- "already been taken" → 「既に使用されています」

### カスタムメッセージの確認

カスタムバリデーションメッセージを設定した場合、そのメッセージが表示されることを確認します。

## よくある問題と対処

### 問題1: バリデーションエラーが表示されない

**原因**:
- バリデーションルールが正しく設定されていない
- フロントエンド側のバリデーションが無効化されている

**対処方法**:
```php
// Resourceクラスのform()メソッドでルールを確認
TextInput::make('name')
    ->required()
    ->maxLength(255),
```

### 問題2: エラーメッセージが英語で表示される

**原因**:
- 言語ファイルが正しく設定されていない
- ロケールが正しく設定されていない

**対処方法**:
```php
// config/app.phpで確認
'locale' => 'ja',

// lang/ja/validation.phpファイルの存在を確認
```

### 問題3: カスタムバリデーションルールが機能しない

**原因**:
- カスタムルールクラスの実装誤り
- ルールが正しく登録されていない

**対処方法**:
```bash
# Laravelログで詳細を確認
./tools/bin/sail-wp admin artisan tail
```

### 問題4: フォームが送信されてしまう（エラーなのに保存される）

**原因**:
- サーバーサイドバリデーションが機能していない
- フロントエンドとサーバーサイドでルールが異なる

**対処方法**:
1. Controllerまたはアクションクラスのバリデーションルールを確認
2. ネットワークリクエストを確認し、サーバーからのレスポンスを検証

## テスト結果の記録

```markdown
### テストケース: バリデーション機能検証

**対象フィールド**: [フィールド名]
**バリデーションルール**: [ルールの説明]

#### 正常値テスト
- **入力値**: [正常な値]
- **結果**: ✅ 成功 / ❌ 失敗
- **スクリーンショット**: `.claude/tmp/validation_success.png`

#### 必須項目テスト
- **入力値**: 空
- **結果**: ❌ エラーメッセージ表示
- **エラーメッセージ**: [表示されたメッセージ]
- **スクリーンショット**: `.claude/tmp/validation_required_error.png`

#### データフォーマットテスト
- **入力値**: [不正な形式の値]
- **結果**: ❌ エラーメッセージ表示
- **エラーメッセージ**: [表示されたメッセージ]
- **スクリーンショット**: `.claude/tmp/validation_format_error.png`

#### 範囲/長さテスト
- **入力値**: [範囲外の値]
- **結果**: ❌ エラーメッセージ表示
- **エラーメッセージ**: [表示されたメッセージ]
- **スクリーンショット**: `.claude/tmp/validation_range_error.png`

**総合評価**: ✅ すべてのバリデーションが正常に機能 / ⚠️ 一部に問題あり / ❌ 重大な問題あり
```

## チェックリスト

- [ ] 正常値でフォーム送信が成功する
- [ ] 必須項目が空の場合、エラーメッセージが表示される
- [ ] メールアドレス形式の検証が機能する
- [ ] URL形式の検証が機能する
- [ ] 数値形式の検証が機能する
- [ ] 日付形式の検証が機能する
- [ ] 最大長の検証が機能する
- [ ] 最小値の検証が機能する
- [ ] 最大値の検証が機能する
- [ ] カスタムバリデーションルールが機能する
- [ ] エラーメッセージが適切な言語で表示される
- [ ] エラーメッセージがフィールドの近くに表示される
- [ ] すべてのテストケースでスクリーンショットを撮影した
- [ ] コンソールエラーが発生していない
