<?php

declare(strict_types=1);

namespace App\Domain\Resource\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory as BaseHasFactory;

/**
 * Modelの名前空間をデフォルトから変更したため、Factoryとの紐付けロジックの調整が必要
 * https://readouble.com/laravel/9.x/ja/eloquent-factories.html#factory-and-model-discovery-conventions
 */
trait HasFactory
{
    use BaseHasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        $modelClass = class_basename(get_called_class());
        $factoryClass = '\\Database\\Factories\\' . $modelClass . 'Factory';

        return $factoryClass::new();
    }
}
