<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Services;

use WonderPlanet\Domain\Common\Facades\WpCommon;
use WonderPlanet\Domain\Currency\Entities\RequestIdDataEntity;
use WonderPlanet\Domain\Currency\Enums\RequestIdType;

/**
 * リクエストIDを取得するサービス
 *
 * ※リクエストごとにIDを保持するインスタンスの管理は、Delegatorおよびそれを利用するFacadeで行う。
 *  本クラスをDI注入しても個別のインスタンスとなるため注意すること
 */
class CurrencyRequestIdService
{
    /**
     * リクエストでユニークなIDとタイプを保持する
     * null(未設定)の場合は、getRequestUniqueIdDataで設定される
     *
     * @var RequestIdDataEntity|null
     */
    private ?RequestIdDataEntity $requestUniqueIdData = null;

    /**
     * nginxなどのフロントにあるシステムからのリクエストIDを保持する
     *
     * @var string|null
     */
    private ?string $frontRequestId = null;

    /**
     * APIリクエストごとにユニークになるIDを取得する
     *
     * リクエストIDが設定されていない場合は自動的に設定される。
     *
     * @return RequestIdDataEntity
     */
    public function getRequestUniqueIdData(): RequestIdDataEntity
    {
        if (is_null($this->requestUniqueIdData)) {
            $reqeustUniqueIdType = '';
            $requestUniqueId = '';

            // クライアントの生成したIDが取得できればそれを設定する
            $configRequestUniqueId = WpCommon::getClientRequestId();

            if ($configRequestUniqueId !== '') {
                $reqeustUniqueIdType = RequestIdType::Product;
                $requestUniqueId = $configRequestUniqueId;
            }

            // nginxのリクエストに付与されるrequest_idを使用する
            //   request_idを取得するには、nginxの設定でfastcgi_paramに値を渡す必要がある。
            //     fastcgi_param REQUEST_ID $request_id;
            // REQUEST_IDが取得できる場合はそれを使用する
            //   request_idであることがわかるよう、req:を付与する
            $nginxRequestId = $this->getFrontRequestId();
            if ($requestUniqueId === '' && $nginxRequestId !== '') {
                $reqeustUniqueIdType = RequestIdType::Request;
                $requestUniqueId = $nginxRequestId;
            }

            // requiest_idが取得できない場合は生成したUUIDを使用する
            if ($requestUniqueId === '') {
                $reqeustUniqueIdType = RequestIdType::Gen;
                $requestUniqueId = WpCommon::getApiRequestId();
            }

            $this->requestUniqueIdData = new RequestIdDataEntity($requestUniqueId, $reqeustUniqueIdType);
        }

        // リクエストIDとタイプを返す
        // 返す時は受け取りを簡略化するため、配列で返す
        return $this->requestUniqueIdData;
    }

    /**
     * nginxなどのフロントにあるシステムからのリクエストIDを取得する
     *
     * @return string
     */
    public function getFrontRequestId(): string
    {
        if (is_null($this->frontRequestId)) {
            $this->frontRequestId = WpCommon::getFrontRequestId();
        }
        return $this->frontRequestId;
    }
}
