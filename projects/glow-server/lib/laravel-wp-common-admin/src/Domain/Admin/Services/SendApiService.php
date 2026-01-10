<?php

namespace WonderPlanet\Domain\Admin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 他環境のapi実行するためのサービス
 */
class SendApiService
{
    /**
     * 指定環境でのadminのAPI実行
     * @param string $domain
     * @param string $endpoint
     *
     * @return array
     */
    public function sendApiRequest(string $domain, string $endpoint): array
    {
        $url = $domain . '/api/' . $endpoint;

        try {
            $response = Http::timeout(900)->get($url);

            if (!$response->successful()) {
                return [
                    'error' => 'API request failed',
                    'status' => $response->status()
                ];
            }
            return $response->json();
        } catch (\Exception $e) {
            Log::error('send api request failed', [$e]);
            return [
                'error' => 'API request failed',
                'status' => $e->getCode(),
            ];
        }
    }
}
