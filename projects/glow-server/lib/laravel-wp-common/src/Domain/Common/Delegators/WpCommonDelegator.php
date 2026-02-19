<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Delegators;

use WonderPlanet\Domain\Common\Services\CommonRequestIdService;

/**
 * 共通ドメインのDelegator
 *
 * staticメソッドではカバーできない共通ドメインのDelegatorを定義する
 *
 * プロダクト側で定義するCommonクラスと重複しないよう、Wpをつけている
 */
class WpCommonDelegator
{
    /**
     * コンストラクタ
     *
     * @param CommonRequestIdService $commonRequestIdService
     */
    public function __construct(
        private readonly CommonRequestIdService $commonRequestIdService,
    ) {
    }

    /**
     * APIリクエストごとにユニークになるIDを取得する
     *
     * @return string
     */
    public function getApiRequestId(): string
    {
        return $this->commonRequestIdService->getApiRequestId();
    }

    /**
     * クライアントが指定したリクエストIDを取得する
     * IDはプロダクト側で指定されたヘッダから取得する
     *
     * @return string
     */
    public function getClientRequestId(): string
    {
        return $this->commonRequestIdService->getClientRequestId();
    }

    /**
     * PHPのフロントにあるミドルウェアのリクエストIDを取得する
     *
     * @return string
     */
    public function getFrontRequestId(): string
    {
        return $this->commonRequestIdService->getFrontRequestId();
    }
}
