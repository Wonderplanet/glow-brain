<?php

declare(strict_types=1);

namespace App\Domain\Shop\Services;

use App\Domain\Common\Constants\System;
use App\Domain\Shop\Constants\AdjustConstant;
use App\Domain\Shop\Constants\WebStoreConstant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Adjust S2S イベント送信サービス
 *
 * WebStore購入時のAdjust S2S (Server-to-Server) イベントトラッキングを行う
 *
 * @see docs/sdd/features/外部決済/adjust.pdf
 */
class AdjustEventService
{
    /**
     * WebStore購入イベントをAdjustに送信
     *
     * @param string $myId プレイヤーID（external_device_idとして使用）
     * @param string|null $currencyCode 通貨コード (ISO 4217)
     * @param int $orderAmount 注文金額（元の金額、100円 = 100）
     * @param string|null $clientIp クライアントIP（custom_parameters.user_ip）
     * @param string|null $osPlatform OSプラットフォーム（iOS/Android）
     * @param string|null $adId 広告ID（IDFA/GAID）
     * @param CarbonImmutable $now 現在日時
     * @param bool $isSandbox サンドボックスモードかどうか
     * @return string Adjust送信ステータス（success/failed/skipped）
     */
    public function sendPurchaseEvent(
        string $myId,
        ?string $currencyCode,
        int $orderAmount,
        ?string $clientIp,
        ?string $osPlatform,
        ?string $adId,
        CarbonImmutable $now,
        bool $isSandbox
    ): string {
        $env = config('app.env');
        if ($env === 'local_test') {
            // テストの場合はAPIリクエストを実行しない
            return AdjustConstant::STATUS_SUCCESS;
        }

        // 無料商品（orderAmount = 0）の場合はスキップ
        if ($orderAmount <= 0) {
            Log::info('Adjust event skipped for free item', [
                'my_id' => $myId,
                'order_amount' => $orderAmount,
            ]);
            return AdjustConstant::STATUS_SKIPPED;
        }

        try {
            // Adjust設定を取得
            $appToken = config('services.adjust.app_token');
            $eventToken = config('services.adjust.event_token');
            $securityToken = config('services.adjust.security_token');

            if (!$appToken || !$eventToken) {
                Log::warning('Adjust configuration missing', [
                    'app_token' => $appToken ? 'set' : 'missing',
                    'event_token' => $eventToken ? 'set' : 'missing',
                ]);
                return AdjustConstant::STATUS_FAILED;
            }

            // パラメータを構築
            $params = $this->buildEventParams(
                $appToken,
                $eventToken,
                $myId,
                $currencyCode ?? 'USD',
                $orderAmount,
                $clientIp,
                $osPlatform,
                $adId,
                $now,
                $isSandbox
            );

            // AdjustにHTTPリクエスト送信
            // セキュリティトークンはAuthorizationヘッダーで送信
            $httpClient = Http::timeout(10);

            if ($securityToken) {
                $httpClient = $httpClient->withHeaders([
                    'Authorization' => 'Bearer ' . $securityToken,
                ]);
            }

            $response = $httpClient->asForm()->post(AdjustConstant::ADJUST_S2S_ENDPOINT, $params);

            if ($response->successful()) {
                Log::info('Adjust event sent successfully', [
                    'my_id' => $myId,
                    'currency' => $currencyCode,
                    'revenue' => $orderAmount,
                    'response_status' => $response->status(),
                ]);
                return AdjustConstant::STATUS_SUCCESS;
            } else {
                Log::warning('Adjust event failed', [
                    'my_id' => $myId,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return AdjustConstant::STATUS_FAILED;
            }
        } catch (\Exception $e) {
            // エラーが発生してもメイン処理をブロックしない
            Log::error('Adjust event exception', [
                'my_id' => $myId,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return AdjustConstant::STATUS_FAILED;
        }
    }

    /**
     * Adjust S2Sイベントパラメータを構築
     *
     * @param string $appToken Adjust App Token
     * @param string $eventToken Adjust Event Token
     * @param string $myId プレイヤーID（external_device_idとして使用）
     * @param string $currencyCode 通貨コード
     * @param int $orderAmount 注文金額
     * @param string|null $clientIp クライアントIP
     * @param string|null $osPlatform OSプラットフォーム（iOS/Android）
     * @param string|null $adId 広告ID（IDFA/GAID）
     * @param CarbonImmutable $now 現在日時
     * @param bool $isSandbox サンドボックスモードかどうか
     * @return array<string, mixed>
     */
    private function buildEventParams(
        string $appToken,
        string $eventToken,
        string $myId,
        string $currencyCode,
        int $orderAmount,
        ?string $clientIp,
        ?string $osPlatform,
        ?string $adId,
        CarbonImmutable $now,
        bool $isSandbox
    ): array {
        // 金額をAdjust形式に変換（例: 100円 -> 1.00）
        $revenue = number_format($orderAmount / AdjustConstant::AMOUNT_DIVISOR, 2, '.', '');

        // ISO 8601形式のタイムスタンプ（URLエンコード済み）
        $createdAt = urlencode($now->toIso8601String());

        $params = [
            's2s' => '1', // S2Sフラグ
            'app_token' => $appToken,
            'event_token' => $eventToken,
            'created_at' => $createdAt,
            'currency' => $currencyCode,
            'revenue' => $revenue,
            'environment' => $isSandbox ? WebStoreConstant::SANDBOX : 'production',
        ];

        // external_device_idとしてmy_idを使用
        // adjust.pdfによると、consent moduleと同じIDを使う必要がある
        $params['external_device_id'] = $myId;

        // クライアントIPアドレス（必須）
        if ($clientIp) {
            $params['ip_address'] = $clientIp;
        }

        // os_name（必須）
        // adjust.pdf: "ユーザーが現在プレイしているOS(iOS、またはAndroid)を設定する"
        if ($osPlatform) {
            $this->addPlatformParams($params, $osPlatform, $adId, $myId);
        } else {
            // osPlatformが未設定の場合は警告ログを出力
            Log::warning('os_platform is null for Adjust event', [
                'my_id' => $myId,
            ]);
        }

        return $params;
    }

    /**
     * プラットフォーム固有のパラメータを追加
     *
     * @param array<string, mixed> $params パラメータ配列（参照渡し）
     * @param string $osPlatform OSプラットフォーム
     * @param string|null $adId 広告ID
     * @param string $myId プレイヤーID
     * @return void
     */
    private function addPlatformParams(array &$params, string $osPlatform, ?string $adId, string $myId): void
    {
        // osPlatformは既にAdjust形式（'ios' or 'android'）で保存されている
        if ($osPlatform === System::PLATFORM_IOS) {
            $params['os_name'] = System::PLATFORM_IOS;
            // iOSの場合はidfaが必須
            // usr_webstore_infosから取得した実際の広告IDを使用（なければデフォルト値）
            $params['idfa'] = $adId ?: AdjustConstant::DEFAULT_AD_ID;
        } elseif ($osPlatform === System::PLATFORM_ANDROID) {
            $params['os_name'] = System::PLATFORM_ANDROID;
            // Androidの場合はgps_adidが必須
            // usr_webstore_infosから取得した実際の広告IDを使用（なければデフォルト値）
            $params['gps_adid'] = $adId ?: AdjustConstant::DEFAULT_AD_ID;
        } else {
            // 不明なプラットフォームの場合はログに記録
            Log::warning('Unknown os_platform for Adjust', [
                'os_platform' => $osPlatform,
                'my_id' => $myId,
            ]);
        }
    }
}
