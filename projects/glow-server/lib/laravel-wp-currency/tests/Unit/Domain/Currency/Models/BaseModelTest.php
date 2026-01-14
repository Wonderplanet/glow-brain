<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BaseModelTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        parent::tearDown();

        // Factoryクラスのresolveをデフォルトに戻す
        $this->setStaticProperty(Factory::class, 'factoryNameResolver', null);
    }

    #[Test]
    public function factory_factoryを返す()
    {
        // Exercise
        $factory = BaseModelTestClass::factory();

        // Verify
        $this->assertInstanceOf('WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories\BaseModelTestClassFactory', $factory);
    }

    #[Test]
    public function factory_プロダクト側のfactoryを返す()
    {
        // Setup
        // プロダクト側で定義されたFacotryを参照するモデルをテストに使う
        $model = new BaseModelTestClass();
        // デフォルトの参照先をテスト用に変更
        Factory::guessFactoryNamesUsing(function ($model) {
            // 存在しているクラス名を返せば良いので固定にする
            // 実際のプロダクト側ではDatabase\Factories\WonderPlanet\Domain\Currency\Models\BaseModelTestClassFactoryのように
            // Database以下に定義、配置されることになる
            return 'WonderPlanet\Tests\Database\Factories\BaseModelTestClassFactory';
        });

        // Exercise
        $factory = $model->factory();

        // Verify
        $this->assertInstanceOf('WonderPlanet\Tests\Database\Factories\BaseModelTestClassFactory', $factory);
    }

    #[Test]
    public function makeNewFactoryClassName_基盤側のfactoryクラス名を返す()
    {
        // Setup
        // 課金・通貨基盤で定義されたFacotryを参照するモデルをテストに使う
        $model = new BaseModelTestClass();

        // Exercise
        $factory = $this->callMethod($model, 'makeNewFactoryClassName');

        // Verify
        $this->assertEquals('WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories\BaseModelTestClassFactory', $factory);
    }

    #[Test]
    public function makeNewFactoryClassName_プロダクト側のfactoryクラス名を返す()
    {
        // Setup
        // プロダクト側で定義されたFacotryを参照するモデルをテストに使う
        $model = new BaseModelTestClass();
        // デフォルトの参照先をテスト用に変更
        Factory::guessFactoryNamesUsing(function ($model) {
            // 存在しているクラス名を返せば良いので固定にする
            // 実際のプロダクト側ではDatabase\Factories\WonderPlanet\Domain\Currency\Models\OprProductFactoryのように定義されている
            return 'WonderPlanet\Tests\Database\Factories\BaseModelTestClassFactory';
        });

        // Exercise
        $factory = $this->callMethod($model, 'makeNewFactoryClassName');

        // Verify
        $this->assertEquals('WonderPlanet\Tests\Database\Factories\BaseModelTestClassFactory', $factory);
    }
}
