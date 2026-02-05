<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Billing\Entities;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Billing\Traits\FakeStoreReceiptTrait;

class StoreReceiptFakeStoreTest extends TestCase
{
    use FakeStoreReceiptTrait;

    public function testGetUnitqueId()
    {
        // Setup
        $receipt = $this->makeFakeStoreReceiptByReceiptString('product1', self::makeFakeStoreReceiptString('unique1'));

        // Exercise
        $uniqueId = $receipt->getUnitqueId();

        // Verify
        $this->assertEquals('unique1', $uniqueId);
    }

    public function testGetPurchaseToken()
    {
        // Setup
        $receipt = $this->makeFakeStoreReceiptByReceiptString('product1', self::makeFakeStoreReceiptString('unique1'));

        // Exercise
        $purchaseToken = $receipt->getPurchaseToken();

        // Verify
        $this->assertEquals('faketoken', $purchaseToken);
    }
}
