<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Xsollaウェブフックの署名検証を行うMiddleware
 */
class XsollaSignatureVerification
{
    /**
     * Xsollaウェブフックの署名を検証する
     *
     * Xsolla仕様に基づく実装:
     * 1. Authorizationヘッダーから "Signature <value>" 形式で署名を取得
     * 2. リクエストボディ（JSON）をそのまま取得
     * 3. SHA-1ハッシュ生成: sha1(body + secretKey)
     * 4. 定数時間比較で検証（タイミング攻撃防止）
     *
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Authorizationヘッダーから署名を取得
        $authorization = $request->header('Authorization');

        if (empty($authorization)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_SIGNATURE',
                    'message' => 'Authorization header is missing',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 2. "Signature "プレフィックスを確認して除去
        if (!str_starts_with($authorization, 'Signature ')) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_SIGNATURE',
                    'message' => 'Invalid Authorization header format. Expected: Signature <value>',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        $signature = substr($authorization, 10); // "Signature " を除去

        if (empty($signature)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_SIGNATURE',
                    'message' => 'Signature value is empty',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Webhook Secretを取得
        $webhookSecret = config('services.xsolla.webhook_secret');

        if (empty($webhookSecret)) {
            // 環境変数が設定されていない場合はエラー
            \Log::error('XSOLLA_WEBHOOK_SECRET is not configured');
            return response()->json([
                'error' => [
                    'code' => 'CONFIGURATION_ERROR',
                    'message' => 'Webhook secret is not configured',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 4. リクエストボディを取得（JSONをパース/再エンコードせず、そのまま使用）
        $requestBody = $request->getContent();

        // 5. SHA-1ハッシュで署名生成: sha1(body + secret)
        $expectedSignature = sha1($requestBody . $webhookSecret);

        // 6. 定数時間比較で検証（タイミング攻撃防止）
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Xsolla webhook signature verification failed', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);

            return response()->json([
                'error' => [
                    'code' => 'INVALID_SIGNATURE',
                    'message' => 'Signature verification failed',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
