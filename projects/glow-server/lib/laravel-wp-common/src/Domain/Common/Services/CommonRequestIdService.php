<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Services;

use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;

/**
 * リクエストIDを管理するサービス
 *
 * ※リクエストごとにIDを保持するインスタンスの管理は、Delegatorおよびそれを利用するFacadeで行う。
 *  本クラスをDI注入しても個別のインスタンスとなるため注意すること
 */
class CommonRequestIdService
{
    /**
     * APIリクエストごとにユニークになるIDを保持する
     *
     * @var string|null
     */
    private ?string $apiRequestId = null;

    /**
     * クライアントが生成したリクエストごとにユニークなIDを保持する
     *
     * @var string
     */
    private ?string $clientRequestId = null;

    /**
     * PHPのフロントにあるミドルウェアのリクエストごとにユニークなIDを取得する
     *
     * @var string|null
     */
    private ?string $frontRequestId = null;

    /**
     * APIリクエストごとにユニークになるIDを取得する
     *
     * @link https://wonderplanet.atlassian.net/wiki/spaces/SEED/pages/337608850#api_request_id%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6
     * @return string
     */
    public function getApiRequestId(): string
    {
        if (is_null($this->apiRequestId)) {
            $this->apiRequestId = Uuid::uuid4()->toString();
        }

        return $this->apiRequestId;
    }

    /**
     * クライアントが指定したリクエストIDを取得する
     * IDはプロダクト側で指定されたヘッダから取得する
     *
     * IDはヘッダから取得するのみとなっている。
     * nginxやALBなど、フロントのミドルウェアのrequest_idとは別に保存している。
     *
     * 今後のログ基盤の設計によってこのメソッドの実装も変更する可能性がある
     *
     * @link https://wonderplanet.atlassian.net/wiki/spaces/SEED/pages/337608850#client_request_id%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6
     * @return string
     */
    public function getClientRequestId(): string
    {
        if (is_null($this->clientRequestId)) {
            $clientRequestId = '';

            // wp_currency.request_unique_id_header_keyが設定されていればそれを使用する
            $configRequestUniqueIdHeaderKey = Config::get('wp_common.request_unique_id_header_key');
            if (!is_null($configRequestUniqueIdHeaderKey)) {
                // headerから取得できればそれを設定する
                $configRequestUniqueId = request()->header($configRequestUniqueIdHeaderKey);

                if (!is_null($configRequestUniqueId) && $configRequestUniqueId !== '') {
                    $clientRequestId = $configRequestUniqueId;
                }
            }

            $this->clientRequestId = $clientRequestId;
        }

        // リクエストIDとタイプを返す
        // 返す時は受け取りを簡略化するため、配列で返す
        return $this->clientRequestId;
    }

    /**
     * PHPのフロントにあるミドルウェアのリクエストIDを取得する
     *
     * まずnginxのリクエストIDを取得し、取得できなければ
     * wp_common.front_request_id_header_keysに設定されているヘッダキーを順番に取得していく
     *
     * @link https://wonderplanet.atlassian.net/wiki/spaces/SEED/pages/337608850#%E3%83%95%E3%83%AD%E3%83%B3%E3%83%88%E3%81%AE%E3%83%AA%E3%82%AF%E3%82%A8%E3%82%B9%E3%83%88%E3%82%92%E3%83%88%E3%83%AC%E3%83%BC%E3%82%B9%E3%81%99%E3%82%8B%E3%81%9F%E3%82%81%E3%81%AEID%E3%81%AB%E3%81%A4%E3%81%84%E3%81%A6
     * @return string
     */
    public function getFrontRequestId(): string
    {
        if (is_null($this->frontRequestId)) {
            $this->frontRequestId = $this->getFrontRequestIdInternal();
        }

        return $this->frontRequestId;
    }

    /**
     * PHPのフロントにあるミドルウェアのリクエストIDを取得する
     * クラス内で使用するため、privateにしている
     *
     * @return string
     */
    private function getFrontRequestIdInternal(): string
    {
        // nginxのリクエストIDを取得
        $nginxRequestId = $_SERVER['REQUEST_ID'] ?? '';

        // nginxのリクエストIDが取得できればそれを使用する
        if ($nginxRequestId !== '') {
            return $nginxRequestId;
        }

        // 設定からフロントリクエストIDのヘッダキーを取得
        $frontRequestIdHeaderKeys = Config::get('wp_common.front_request_id_header_keys');
        // ヘッダキーが設定されていなければ空文字を返す
        if (is_null($frontRequestIdHeaderKeys) || $frontRequestIdHeaderKeys === '') {
            return '';
        }

        // ヘッダキーが配列でなければ空文字を返す
        if (!is_array($frontRequestIdHeaderKeys)) {
            return '';
        }

        // ヘッダキーを順番に取得していく
        $request = request();
        foreach ($frontRequestIdHeaderKeys as $key) {
            // ヘッダキーが空文字でなければその値を返す
            if ($key !== '') {
                $frontRequestId = $request->header($key, '');
                if ($frontRequestId !== '') {
                    return $frontRequestId;
                }
            }
        }

        // どれも取得できなければ空文字を返す
        return '';
    }
}
