<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseEloquentModel;

/**
 * 各モデルの共通クラス
 */
abstract class BaseModel extends BaseEloquentModel
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    /**
     * コネクション名を取得する
     * 内部的にコンストラクタで使用するためのメソッド
     *
     * @return string
     */
    abstract protected function getConnNameInternal(): string;

    /**
     *
     * @param array<mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 接続先を設定にあるものにする
        // コンストラクタで変更しているので、staticメソッドのデフォルトインスタンスも変更されている
        $this->connection = $this->getConnNameInternal();
    }

    /**
     * Factoryモデルの取得
     *
     * クラスのnamespaceからModelsを抜いて、Database\Factoriesを付けたものを返す
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        $factoryClass = self::makeNewFactoryClassName();

        return $factoryClass::new();
    }

    /**
     * Factoryモデルのクラス名を取得
     *
     * 次の順番で存在するクラス名を返す
     *
     * 1. プロダクト側で定義されたFactoryクラス
     *    Factory::resolveFactoryName()で取得されたクラス名を使用する
     *    例: Database\Factories\WonderPlanet\Domain\Currency\Models\OprProductFactory
     *
     * 2. Factoryクラス
     *    例: WonderPlanet\Domain\Currency\Database\Factories\OprProductFactory
     *
     * @return string
     */
    protected static function makeNewFactoryClassName(): string
    {
        $modelClass = get_called_class();

        // プロダクト側デフォルトのFactoryモデルのクラス名を参照する
        // プロダクト側で用意されたFactoryクラスを読み込むため
        //   例: Database\Factories\WonderPlanet\Domain\Currency\Models\OprProductFactory
        $factoryClass = Factory::resolveFactoryName($modelClass);
        if (class_exists($factoryClass)) {
            return $factoryClass;
        }

        // Factoryモデルの取得
        // クラスのnamespaceからModelsを抜いて、Database\Factoriesを付けたものを返す
        //   例: WonderPlanet\Domain\Currency\Models\OprProductFactory
        //       -> WonderPlanet\Domain\Currency\Database\Factories\OprProductFactory
        $className = class_basename($modelClass);
        $namespace = substr($modelClass, 0, strrpos($modelClass, $className) - 1);
        $factoryClass = str_replace('Models', 'Database\Factories', $namespace) . '\\' . $className . 'Factory';

        return $factoryClass;
    }
}
