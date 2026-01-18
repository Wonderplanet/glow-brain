# カスタムパラメータ処理

## 概要

Xsollaウェブフックのリクエストには `custom_parameters` フィールドが含まれます。これらのパラメータを適切に処理・検証する方法を説明します。

## カスタムパラメータ一覧

### user_ip

**型**: string

**説明**: ユーザーのIPアドレス

**用途**:
- 不正購入防止のためのログ記録
- Adjustへの送信

**処理方法**:
```php
$userIp = $request->input('custom_parameters.user_ip');
// IPアドレスのバリデーション
if (!filter_var($userIp, FILTER_VALIDATE_IP)) {
    // エラーハンドリング
}
```

### store_code

**型**: string

**説明**: ストアコード（決済プラットフォームの識別子）

**値の種類**:

| 環境 | ストアコード |
|------|-------------|
| アソビストア | `asobi-store` |
| Bandai Namco Entertainment WebStore（日本） | `web-store-jp` |
| Bandai Namco Entertainment WebStore（海外） | `web-store-global` |

**用途**:
- ストア種別の識別
- 年齢制限ルールの適用（ストアごとに異なる）
- ログ記録

**処理方法**:
```php
$storeCode = $request->input('custom_parameters.store_code');

// 許可されたストアコードのチェック
$allowedStores = ['asobi-store', 'web-store-jp', 'web-store-global'];
if (!in_array($storeCode, $allowedStores, true)) {
    // エラーハンドリング
}
```

## バリデーションルール

### Laravel Validation

```php
$validated = $request->validate([
    'custom_parameters' => 'required|array',
    'custom_parameters.user_ip' => 'required|ip',
    'custom_parameters.store_code' => 'required|in:asobi-store,web-store-jp,web-store-global',
]);
```

## 実装例

### カスタムパラメータの取得と検証

```php
public function handle(Request $request): JsonResponse
{
    // カスタムパラメータのバリデーション
    try {
        $validated = $request->validate([
            'notification_type' => 'required|string',
            'user' => 'required|array',
            'custom_parameters' => 'required|array',
            'custom_parameters.user_ip' => 'required|ip',
            'custom_parameters.store_code' => 'required|string',
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'error' => [
                'code' => 'INVALID_PARAMETER',
                'message' => 'Invalid custom parameters',
            ]
        ], 400);
    }

    $userIp = $validated['custom_parameters']['user_ip'];
    $storeCode = $validated['custom_parameters']['store_code'];

    // ストアコードに基づいた処理
    $ageRestrictionService = $this->getAgeRestrictionService($storeCode);

    // 処理を続行...
}

private function getAgeRestrictionService(string $storeCode): AgeRestrictionServiceInterface
{
    return match ($storeCode) {
        'asobi-store', 'web-store-jp' => new JapanAgeRestrictionService(),
        'web-store-global' => new GlobalAgeRestrictionService(),
        default => throw new \InvalidArgumentException("Unknown store code: {$storeCode}"),
    };
}
```

## 注意事項

### ✅ 推奨事項

- カスタムパラメータは必ず検証する
- IPアドレスは `filter_var()` または Laravelの `ip` ルールで検証
- store_codeは許可リストでチェック
- ログ記録時にカスタムパラメータを含める

### ❌ 禁止事項

- カスタムパラメータを検証せずに使用
- SQLクエリに直接埋め込む（SQLインジェクション対策）
- 信頼できる値として扱う（必ず検証）

## トラブルシューティング

### custom_parametersが存在しない

**原因**: Xsolla側の設定ミス

**対処**: Xsollaのウェブフック設定でカスタムパラメータが正しく設定されているか確認

### store_codeが想定外の値

**原因**: 新しいストアが追加された、または設定ミス

**対処**: 許可リストを更新、またはXsolla設定を確認
