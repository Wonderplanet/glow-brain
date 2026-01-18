<?php
declare(strict_types=1);

namespace App\Http\ResponseFactories;

use WonderPlanet\Domain\Billing\Entities\UsrStoreInfoEntity;

trait StoreInfoResponderTrait
{
    /**
     * ショップ情報のレスポンスを共通化する
     *
     * @param UsrStoreInfoEntity $usrStoreInfo
     * @return array{age: int, paid_price: string, renotify_at: string|null}
     */
    private function createStoreInfoResponse(UsrStoreInfoEntity $usrStoreInfo): array
    {
        $result = [
            'age' => $usrStoreInfo->age,
            'paid_price' => (string)$usrStoreInfo->paid_price,
            'renotify_at' => $usrStoreInfo->renotify_at,
        ];

        return $result;
    }
}
