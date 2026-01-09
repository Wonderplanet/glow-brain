<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Models;

trait HasEntityTrait
{
    /**
     * クラス名に対応するEntityオブジェクトを作成して返す
     *
     * @return object
     */
    public function getModelEntity(): object
    {
        $modelClass = get_called_class();
        $className = class_basename($modelClass);
        $namespace = substr($modelClass, 0, strrpos($modelClass, $className) - 1);
        $entityClass = str_replace('Models', 'Entities', $namespace) . '\\' . $className . 'Entity';

        return new $entityClass($this);
    }
}
