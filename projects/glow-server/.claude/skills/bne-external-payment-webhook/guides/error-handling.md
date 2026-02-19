# エラーハンドリング

## エラーレスポンスの基本形式

Xsollaへのエラーレスポンスは以下の形式で返します：

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message"
  }
}
```

## 主なエラーケースとHTTPステータスコード

### 1. ユーザーが見つからない（404 Not Found）

```json
{
  "error": {
    "code": "INVALID_USER",
    "message": "User not found"
  }
}
```

**HTTPステータス**: `404`

**発生ケース**:
- バンダイナムコIDに対応するglow-serverユーザーが存在しない
- ユーザーIDの形式が不正

### 2. パラメータ不正（400 Bad Request）

```json
{
  "error": {
    "code": "INVALID_PARAMETER",
    "message": "Invalid request parameters"
  }
}
```

**HTTPステータス**: `400`

**発生ケース**:
- 必須パラメータが欠けている
- パラメータの型が不正
- バリデーションエラー

### 3. 署名検証失敗（401 Unauthorized）

```json
{
  "error": {
    "code": "INVALID_SIGNATURE",
    "message": "Signature verification failed"
  }
}
```

**HTTPステータス**: `401`

**発生ケース**:
- Signatureヘッダーが欠けている
- 署名が一致しない

### 4. 年齢制限エラー（403 Forbidden）

```json
{
  "error": {
    "code": "AGE_RESTRICTION",
    "message": "User does not meet age requirements"
  }
}
```

**HTTPステータス**: `403`

**発生ケース**:
- 18歳未満のユーザーが有料アイテムを購入しようとした（日本ストア）
- 13歳以下のユーザーがログインしようとした（海外ストア）

### 5. サーバーエラー（500 Internal Server Error）

```json
{
  "error": {
    "code": "SERVER_ERROR",
    "message": "Internal server error"
  }
}
```

**HTTPステータス**: `500`

**発生ケース**:
- データベース接続エラー
- 予期しない例外

## 実装例

### エラーレスポンス作成用のヘルパー

```php
<?php

declare(strict_types=1);

namespace App\Http\ResponseFactories;

use Illuminate\Http\JsonResponse;

class WebStoreWebhookResponseFactory
{
    public function createErrorResponse(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ], $status);
    }

    public function createUserNotFoundError(): JsonResponse
    {
        return $this->createErrorResponse(
            'INVALID_USER',
            'User not found',
            404
        );
    }

    public function createInvalidParameterError(string $detail = ''): JsonResponse
    {
        $message = 'Invalid request parameters';
        if ($detail) {
            $message .= ': ' . $detail;
        }
        return $this->createErrorResponse(
            'INVALID_PARAMETER',
            $message,
            400
        );
    }

    public function createAgeRestrictionError(): JsonResponse
    {
        return $this->createErrorResponse(
            'AGE_RESTRICTION',
            'User does not meet age requirements',
            403
        );
    }

    public function createServerError(): JsonResponse
    {
        return $this->createErrorResponse(
            'SERVER_ERROR',
            'Internal server error',
            500
        );
    }
}
```

### コントローラーでの使用例

```php
public function handleUserValidation(Request $request): JsonResponse
{
    try {
        // バリデーション
        $validated = $request->validate([
            'user.user_id' => 'required|string',
            'user.mbid' => 'required|string',
            // ...
        ]);

        $userId = $validated['user']['user_id'];

        // ユーザー取得
        $user = $this->userRepository->findByBnid($userId);

        if (!$user) {
            return $this->responseFactory->createUserNotFoundError();
        }

        // 正常レスポンス
        return $this->responseFactory->createUserValidationResponse($user);

    } catch (ValidationException $e) {
        return $this->responseFactory->createInvalidParameterError($e->getMessage());
    } catch (\Exception $e) {
        Log::error('WebStore webhook error', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return $this->responseFactory->createServerError();
    }
}
```

## ログ記録

エラー発生時は必ず詳細なログを記録します：

```php
Log::error('WebStore webhook error', [
    'notification_type' => $request->input('notification_type'),
    'user_id' => $request->input('user.user_id'),
    'store_code' => $request->input('custom_parameters.store_code'),
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
]);
```

## 注意事項

### ✅ 推奨事項

- エラーコードは大文字スネークケース（`INVALID_USER`）
- ログには十分な情報を記録（notification_type, user_id, store_code等）
- 本番環境ではスタックトレースをレスポンスに含めない
- 予期しない例外は500エラーで処理

### ❌ 禁止事項

- エラーレスポンスに詳細なスタックトレースを含める（セキュリティリスク）
- エラーログを記録しない
- すべてのエラーを500で返す（適切なステータスコードを使用）

## Xsollaのリトライ動作

Xsollaは以下の条件でウェブフックをリトライします：

- HTTPステータスコードが5xxの場合
- タイムアウト（30秒）

**リトライ回数**: 最大3回

**リトライ間隔**: 指数バックオフ（1分、2分、4分）

そのため、500エラーを返す場合は冪等性を保つ実装が重要です。
