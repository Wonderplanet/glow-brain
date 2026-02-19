# CRUD機能のテスト

CRUD（作成・読取・更新・削除）機能を実装したFilamentリソースのテスト手順を説明します。

## 対象となる実装

以下のような実装後にこのテストパターンを使用します：

- 新規Filamentリソースの追加
- 既存リソースへのCRUD機能追加
- CRUD操作の変更・改善

## テストの目的

CRUD機能が以下を満たすことを確認：

1. データ一覧が正しく表示される
2. 新規作成フォームが正常に動作する
3. データが正しく保存される
4. 編集フォームが正常に動作する
5. データが正しく更新される
6. 削除機能が正常に動作する

## 事前準備

1. **ログインとページ遷移**
   - 標準ログイン手順を実行
   - 対象リソースのページへ遷移
   - 参照: **[標準ログイン手順](login.md)**, **[新規ページ/リソース追加のテスト](new-resource.md)**

2. **既存データの確認**
   - テーブルに既存データが存在するか確認
   - 存在しない場合は、手動またはSeederでテストデータを作成

## テスト手順

### フェーズ1: データ一覧の確認

#### ステップ1.1: 一覧ページの表示確認

```javascript
// スナップショット取得
take_snapshot()
```

**確認項目**:
- テーブルが表示されている
- カラムヘッダーが正しい
- データ行が表示されている（データが存在する場合）
- "No records found"等のメッセージが表示される（データがない場合）

#### ステップ1.2: テーブルの内容確認

**確認項目**:
- 各カラムにデータが正しく表示されている
- 日時データが適切なフォーマットで表示されている
- 外部キー関連のデータが正しく表示されている（リレーション）
- アクションボタン（編集/削除）が表示されている

#### ステップ1.3: スクリーンショット撮影

```javascript
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/list_view.png"
})
```

### フェーズ2: 新規作成機能の確認

#### ステップ2.1: 新規作成ボタンをクリック

```javascript
// 「新規作成」ボタンをクリック
click({
  uid: "create_button_uid"  // スナップショットから取得
})
```

一般的なボタンテキスト:
- "Create" / "新規作成"
- "New" / "新しい"
- "Add" / "追加"

#### ステップ2.2: フォーム表示完了を待機

```javascript
// フォームが表示されるまで待機
wait_for({
  text: "Create",  // フォームのタイトル
  timeout: 5000
})
```

#### ステップ2.3: フォームの表示確認

```javascript
// スナップショット取得
take_snapshot({ verbose: true })
```

**確認項目**:
- すべてのフォームフィールドが表示されている
- 必須項目に「*」マークまたは「Required」が表示されている
- セレクトボックスに選択肢が表示されている
- デフォルト値が設定されている（該当する場合）

#### ステップ2.4: フォームにテストデータを入力

各フィールドにテストデータを入力します。

**テキスト入力の例**:
```javascript
fill({
  uid: "name_field_uid",
  value: "Test Product Name"
})
```

**数値入力の例**:
```javascript
fill({
  uid: "price_field_uid",
  value: "1000"
})
```

**セレクトボックスの例**:
```javascript
fill({
  uid: "category_field_uid",
  value: "category_1"  // option valueを指定
})
```

**日付入力の例**:
```javascript
fill({
  uid: "published_date_field_uid",
  value: "2025-01-30"
})
```

**テキストエリアの例**:
```javascript
fill({
  uid: "description_field_uid",
  value: "This is a test description for the product."
})
```

#### ステップ2.5: フォーム入力後のスクリーンショット

```javascript
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/create_form_filled.png"
})
```

#### ステップ2.6: 保存ボタンをクリック

```javascript
// 保存ボタンをクリック
click({
  uid: "save_button_uid"  // スナップショットから取得
})
```

一般的なボタンテキスト:
- "Create" / "作成"
- "Save" / "保存"
- "Submit" / "送信"

#### ステップ2.7: 成功メッセージの確認

```javascript
// 成功メッセージが表示されるまで待機
wait_for({
  text: "created successfully",  // または「作成されました」
  timeout: 5000
})
```

#### ステップ2.8: 成功後の画面確認

```javascript
// スナップショット取得
take_snapshot()

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/create_success.png"
})
```

**確認項目**:
- 成功メッセージが表示されている
- 一覧ページに戻る、または詳細ページに遷移している
- 作成したデータが一覧に表示されている（一覧ページの場合）

### フェーズ3: 編集機能の確認

#### ステップ3.1: 作成したデータの編集ボタンをクリック

一覧ページから、先ほど作成したデータの編集ボタンをクリックします。

```javascript
// 編集ボタンをクリック
click({
  uid: "edit_button_uid"  // スナップショットから取得
})
```

#### ステップ3.2: 編集フォームの表示確認

```javascript
// フォームが表示されるまで待機
wait_for({
  text: "Edit",  // フォームのタイトル
  timeout: 5000
})

// スナップショット取得
take_snapshot({ verbose: true })
```

**確認項目**:
- フォームに既存データが入っている
- すべてのフィールドが正しい値を表示している
- セレクトボックスで正しい選択肢が選ばれている

#### ステップ3.3: データを変更

1つ以上のフィールドを変更します。

```javascript
// 例: 名前を変更
fill({
  uid: "name_field_uid",
  value: "Updated Product Name"
})

// 例: 価格を変更
fill({
  uid: "price_field_uid",
  value: "1500"
})
```

#### ステップ3.4: 更新ボタンをクリック

```javascript
// 更新ボタンをクリック
click({
  uid: "save_button_uid"
})
```

一般的なボタンテキスト:
- "Save" / "保存"
- "Update" / "更新"

#### ステップ3.5: 更新成功メッセージの確認

```javascript
// 成功メッセージが表示されるまで待機
wait_for({
  text: "updated successfully",  // または「更新されました」
  timeout: 5000
})

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/update_success.png"
})
```

**確認項目**:
- 成功メッセージが表示されている
- 更新後のデータが正しく表示されている

### フェーズ4: 削除機能の確認

#### ステップ4.1: 削除ボタンをクリック

一覧ページまたは詳細ページから、削除ボタンをクリックします。

```javascript
// 削除ボタンをクリック
click({
  uid: "delete_button_uid"
})
```

#### ステップ4.2: 確認ダイアログの処理（該当する場合）

確認ダイアログが表示される場合は、確認ボタンをクリックします。

```javascript
// 確認ダイアログの「削除」ボタンをクリック
click({
  uid: "confirm_delete_button_uid"
})
```

#### ステップ4.3: 削除成功メッセージの確認

```javascript
// 成功メッセージが表示されるまで待機
wait_for({
  text: "deleted successfully",  // または「削除されました」
  timeout: 5000
})

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/delete_success.png"
})
```

#### ステップ4.4: 一覧からデータが削除されたことを確認

```javascript
// スナップショット取得
take_snapshot()
```

**確認項目**:
- 削除したデータが一覧に表示されていない
- 他のデータには影響がない

## エラーケースのテスト（推奨）

### 新規作成時のバリデーションエラー

#### ステップ1: 必須項目を空で送信

```javascript
// 新規作成フォームを開く
click({ uid: "create_button_uid" })

// 何も入力せずに保存ボタンをクリック
click({ uid: "save_button_uid" })
```

#### ステップ2: バリデーションエラーメッセージの確認

```javascript
// エラーメッセージが表示されるまで待機
wait_for({
  text: "required",  // または「必須です」
  timeout: 3000
})

// スクリーンショット撮影
take_screenshot({
  format: "png",
  fullPage: true,
  filePath: ".claude/tmp/validation_error.png"
})
```

**確認項目**:
- 各必須項目にエラーメッセージが表示されている
- エラーメッセージが日本語（または適切な言語）で表示されている
- フォームが送信されていない（一覧ページに戻っていない）

## よくある問題と対処

### 問題1: フォームが表示されない

**原因**:
- `form()`メソッドの定義が誤っている
- Livewireのレンダリングエラー

**対処方法**:
```bash
# Laravelログを確認
./tools/bin/sail-wp admin artisan tail
```

### 問題2: データが保存されない

**原因**:
- モデルの`$fillable`が設定されていない
- データベース制約違反
- バリデーションエラー

**対処方法**:
1. モデルの`$fillable`プロパティを確認
2. データベースマイグレーションを確認
3. バリデーションルールを確認

### 問題3: リレーションデータが表示されない

**原因**:
- Eloquentリレーションが正しく定義されていない
- N+1問題（クエリが最適化されていない）

**対処方法**:
```php
// Resourceクラスでリレーションを事前読み込み
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->with(['category', 'tags']);
}
```

### 問題4: 削除が失敗する

**原因**:
- 外部キー制約で削除が防止されている
- ソフトデリート設定の問題

**対処方法**:
1. データベース制約を確認
2. ソフトデリートが有効か確認
3. 削除前に関連データを削除する必要があるか確認

## コンソールエラーとネットワークリクエストの確認

各フェーズの完了後、以下を確認することを推奨：

```javascript
// コンソールエラーを確認
list_console_messages({
  types: ["error"],
  pageSize: 50
})

// ネットワークリクエストを確認
list_network_requests({
  resourceTypes: ["xhr", "fetch"],
  pageSize: 50
})
```

## テスト結果の記録

```markdown
### テストケース: CRUD機能検証

**対象**: [リソース名]

**フェーズ1: 一覧表示**
- **結果**: ✅ 成功 / ❌ 失敗
- **確認項目**: テーブル表示、カラムヘッダー、データ行
- **スクリーンショット**: `.claude/tmp/list_view.png`

**フェーズ2: 新規作成**
- **結果**: ✅ 成功 / ❌ 失敗
- **確認項目**: フォーム表示、データ入力、保存成功
- **テストデータ**: [入力したデータの概要]
- **スクリーンショット**:
  - `.claude/tmp/create_form_filled.png`
  - `.claude/tmp/create_success.png`

**フェーズ3: 編集**
- **結果**: ✅ 成功 / ❌ 失敗
- **確認項目**: フォームに既存データ、データ変更、更新成功
- **スクリーンショット**: `.claude/tmp/update_success.png`

**フェーズ4: 削除**
- **結果**: ✅ 成功 / ❌ 失敗
- **確認項目**: 削除ボタン、確認ダイアログ、削除成功、一覧から消失
- **スクリーンショット**: `.claude/tmp/delete_success.png`

**エラー検証**
- **コンソールエラー**: なし / [エラー内容]
- **ネットワークエラー**: なし / [エラー内容]
```

## チェックリスト

- [ ] 一覧ページが正しく表示される
- [ ] 新規作成フォームが表示される
- [ ] データを入力して保存できる
- [ ] 成功メッセージが表示される
- [ ] 作成したデータが一覧に表示される
- [ ] 編集フォームに既存データが表示される
- [ ] データを変更して更新できる
- [ ] 更新成功メッセージが表示される
- [ ] 削除ボタンが機能する
- [ ] 削除後、一覧からデータが消える
- [ ] バリデーションエラーが適切に表示される
- [ ] コンソールエラーが発生していない
- [ ] ネットワークエラーが発生していない
- [ ] すべてのフェーズでスクリーンショットを撮影した
