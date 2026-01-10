<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use WonderPlanet\Domain\Currency\Repositories\UsrCurrencySummaryRepository;

class HasEntityTraitTest extends TestCase
{
    use RefreshDatabase;

    private UsrCurrencySummaryRepository $usrCurrencySummaryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->usrCurrencySummaryRepository = $this->app->make(UsrCurrencySummaryRepository::class);
    }

    #[Test]
    public function getModelEntity_対応するEntityを返す()
    {
        // Setup
        //  usrCurrencySummaryをテストとして使用する
        $this->usrCurrencySummaryRepository->insertCurrencySummary('1', 100, 110);
        $usrCurrencySummary = $this->usrCurrencySummaryRepository->findByUserId('1');

        // Exercise
        $usrCurrencySummaryEntity = $usrCurrencySummary->getModelEntity();

        // Verify
        $this->assertEquals('1', $usrCurrencySummaryEntity->getUserId());
        $this->assertEquals(100, $usrCurrencySummaryEntity->getFreeAmount());
    }
}
