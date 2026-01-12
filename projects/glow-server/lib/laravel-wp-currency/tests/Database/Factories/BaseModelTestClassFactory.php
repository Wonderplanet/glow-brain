<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Tests\Unit\Domain\Currency\Models\BaseModelTestClass;

/**
 * プロダクト側で用意したBaseModelTestClassクラスの想定
 *
 * ※プロダクト側でFactoryクラスを入れ替えるサンプルとして作成
 * Database\Factoriesの下に作成することで、課金基盤ライブラリ内で使用するFactoryクラスをプロダクト側で作成することができる
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\WonderPlanet\Tests\Unit\Domain\Currency\Models\BaseModelTestClass>
 */
class BaseModelTestClassFactory extends Factory
{
    protected $model = BaseModelTestClass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [];
    }
}
