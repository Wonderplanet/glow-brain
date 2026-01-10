<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Traits;

use WonderPlanet\Domain\Billing\Entities\StoreReceiptFakeStore;

/**
 * UnityのFakeStore向けの処理をまとめたTrait
 */
trait FakeStoreReceiptTrait
{
    /**
     * FakeStoreのレシート文字列を作成する
     *
     * @param string $uniqueId
     * @return string
     */
    public static function makeFakeStoreReceiptString(string $uniqueId = ''): string
    {
        if ($uniqueId === '') {
            $uniqueId = uniqid();
        }

        $receipt = <<< EOM
        {
            "Payload":"ThisIsFakeReceiptData",
            "Store":"fake",
            "TransactionID":"{$uniqueId}"
        }
        EOM;

        return $receipt;
    }

    /**
     * FakeStoreのレシートオブジェクトを作成する
     *
     * $uniqueIdを指定しない場合は自動的に割り振られる
     *
     * @param string $productId
     * @param string $uniqueId
     * @return StoreReceiptFakeStore
     */
    private function makeFakeStoreReceipt(string $productId, string $uniqueId = ''): StoreReceiptFakeStore
    {
        $receipt = self::makeFakeStoreReceiptString($uniqueId);

        // Fake Store用のレシートは、レシート文字列をそのままStoreReceiptオブジェクトに詰めて返す
        return new StoreReceiptFakeStore($productId, $receipt, json_decode($receipt, true));
    }

    /**
     * FakeStoreを継承した、isSandboxReceiptをfalseにしたレシートオブジェクトを作成する
     *
     * 集計などでサンドボックスレシートではない場合をテストするために使用する
     * クラスとして用意しないのは、不用意に使用されないようにするため
     *
     * @param string $productId
     * @param string $uniqueId
     * @return StoreReceiptFakeStore
     */
    private function makeFakeStoreReceiptNoSandbox(string $productId, string $uniqueId = ''): StoreReceiptFakeStore
    {
        $receipt = self::makeFakeStoreReceiptString($uniqueId);

        // StoreReceiptFakeStoreを継承して、isSandboxReceiptをfalseにする
        $storeReceipt = new class ($productId, $receipt, json_decode($receipt, true)) extends StoreReceiptFakeStore {
            public function isSandboxReceipt(): bool
            {
                return false;
            }
        };

        return $storeReceipt;
    }

    /**
     * FakeStoreのレシートオブジェクトをレシート文字列から作成する
     *
     * @param string $productId
     * @param string $receipt
     * @return StoreReceiptFakeStore
     */
    private function makeFakeStoreReceiptByReceiptString(string $productId, string $receipt): StoreReceiptFakeStore
    {
        return new StoreReceiptFakeStore($productId, $receipt, json_decode($receipt, true));
    }


    /**
     * Fake Storeのレシートかを判定する
     *
     * @param string $receipt
     * @return boolean
     */
    public function isFakeStoreReceipt(string $receipt): bool
    {
        $receiptData = json_decode($receipt, true);

        return (($receiptData['Payload'] ?? '') === 'ThisIsFakeReceiptData')
            && (($receiptData['Store'] ?? '') === 'fake');
    }
}
