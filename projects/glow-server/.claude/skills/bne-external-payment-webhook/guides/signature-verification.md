# 署名検証（Signature Verification）

## 概要

Xsollaからのウェブフックリクエストが正当なものであることを確認するため、SHA-1ハッシュによる署名検証を実装します。

## 署名の仕組み

Xsollaは以下の手順で署名を生成します：

1. リクエストのJSON本文を取得
2. プロジェクトの秘密鍵（Secret Key）と連結
3. 連結した文字列にSHA-1ハッシュを適用
4. 結果をHTTPヘッダー `Signature` に設定して送信

## 検証手順

glow-server側では、同じ手順で署名を生成し、リクエストヘッダーの署名と比較します。

### 1. 署名検証ミドルウェアの作成

**ファイルパス**: `api/app/Http/Middleware/VerifyXsollaSignature.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyXsollaSignature
{
    /**
     * Xsolla Webhookの署名を検証
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Signature');

        if (!$signature) {
            Log::warning('Xsolla webhook: Signature header missing');
            return response()->json([
                'error' => ['code' => 'INVALID_SIGNATURE', 'message' => 'Signature header is required']
            ], 401);
        }

        // リクエストボディを取得
        $content = $request->getContent();

        // 秘密鍵を取得（環境変数から）
        $secretKey = config('services.xsolla.webhook_secret');

        // 署名を生成
        $calculatedSignature = sha1($content . $secretKey);

        // 署名を比較
        if (!hash_equals($calculatedSignature, $signature)) {
            Log::warning('Xsolla webhook: Invalid signature', [
                'expected' => $calculatedSignature,
                'received' => $signature,
            ]);
            return response()->json([
                'error' => ['code' => 'INVALID_SIGNATURE', 'message' => 'Signature verification failed']
            ], 401);
        }

        return $next($request);
    }
}
```

### 2. 秘密鍵の設定

**ファイルパス**: `api/config/services.php`

```php
'xsolla' => [
    'webhook_secret' => env('XSOLLA_WEBHOOK_SECRET'),
],
```

**ファイルパス**: `api/.env`

```
XSOLLA_WEBHOOK_SECRET=your_secret_key_here
```

### 3. ミドルウェアの登録

**ファイルパス**: `api/app/Http/Kernel.php` または `api/bootstrap/app.php`（Laravel 11）

```php
protected $middlewareAliases = [
    // ...
    'xsolla.signature' => \App\Http\Middleware\VerifyXsollaSignature::class,
];
```

### 4. ルーティングへの適用

**ファイルパス**: `api/routes/api.php`

```php
Route::post('/webstore/webhooks', [WebStoreWebhookController::class, 'handle'])
    ->middleware('xsolla.signature');
```

## セキュリティ上の注意点

### ✅ 推奨事項

- **hash_equals()を使用**: タイミング攻撃を防ぐため、署名比較には必ず `hash_equals()` を使用
- **秘密鍵の保護**: XSOLLA_WEBHOOK_SECRETは.envファイルで管理し、Gitにコミットしない
- **ログ記録**: 署名検証失敗時は必ずログに記録し、不正アクセスを監視

### ❌ 禁止事項

- `==` や `===` での署名比較（タイミング攻撃に脆弱）
- 秘密鍵をコードにハードコーディング
- 署名検証のスキップ（開発環境でも検証を実施）

## テスト実装

署名検証のテストについては **[examples/signature-verification.md](../examples/signature-verification.md)** を参照してください。

## トラブルシューティング

### 署名検証が常に失敗する

**原因1**: リクエストボディの文字エンコーディング問題

→ `$request->getContent()` で生のボディを取得していることを確認

**原因2**: 秘密鍵が間違っている

→ Xsolla管理画面で秘密鍵を確認し、.envファイルに正しく設定

**原因3**: Xsolla側の設定ミス

→ Xsollaのウェブフック設定で正しいURLと秘密鍵が設定されているか確認

## 参考資料

- Xsolla公式ドキュメント: https://developers.xsolla.com/api/v2/getting-started/#api_webhooks_webhooks_security
