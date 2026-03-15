<?php

declare(strict_types=1);

namespace App\Domain\Shop\UseCases;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Shop\Services\WebStoreUserService;
use App\Http\Responses\ResultData\ShopWebstoreUserValidationResultData;

/**
 * WebStore W1: ユーザー情報取得
 *
 * notification_type: web_store_user_validation
 */
class WebStoreUserValidationUseCase
{
    public function __construct(
        private readonly WebStoreUserService $webStoreUserService,
    ) {
    }

    /**
     * W1: ユーザー情報取得
     *
     * @param string $bnUserId BN User ID
     * @return ShopWebstoreUserValidationResultData
     * @throws GameException
     */
    public function exec(string $bnUserId): ShopWebstoreUserValidationResultData
    {
        return $this->webStoreUserService->validateUser($bnUserId);
    }
}
