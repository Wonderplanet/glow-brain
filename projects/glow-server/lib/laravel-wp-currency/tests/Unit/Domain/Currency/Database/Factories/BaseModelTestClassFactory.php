<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Unit\Domain\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WonderPlanet\Tests\Unit\Domain\Currency\Models\BaseModelTestClass;

/**
 * BaseModelTestのために作成したテスト用のFactoryクラス
 * 
 * 課金基盤ライブラリ本体の処理では使用しない想定
 * ユニットテストでFactoryクラスの読み込みを行うために作成
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
        // DBアクセスをさせるつもりがないため、空配列を返す
        return [];
    }
}
