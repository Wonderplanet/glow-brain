<?php

declare(strict_types=1);

namespace WonderPlanet\Tests\Traits;

use ReflectionClass;

/**
 * リフレクション操作をテストで行う時に使用するトレイト
 */
trait ReflectionTrait
{
    /**
     * objectのメソッドを呼び出す
     * (privateメソッドも呼び出せる)
     *
     * @param string|object $object
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public function callMethod(string|object $object, string $methodName, array $args=[]): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs(
            is_string($object) ? null : $object,
            $args
        );
    }

    /**
     * staticメソッドを呼び出す
     * (privateメソッドも呼び出せる)
     *
     * @param object|string $object
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public function callStaticMethod(object|string $object, string $methodName, array $args=[]): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $args);
    }

    /**
     * 指定したオブジェクトのメンバ変数の値を取得する
     *
     * @param object $target 対象のインスタンス
     * @param string $propertyName 取得するプロパティ名
     * @return mixed プロパティの値
     */
    public function getProperty(object $target, string $propertyName)
    {
        $reflection = new ReflectionClass($target);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($target);
    }

    /**
     * 指定したオブジェクトのメンバ変数の値を設定する
     *
     * @param object $target 対象のインスタンス
     * @param string $propertyName 設定するプロパティ名
     * @param mixed $value 設定する値
     */
    public function setProperty(object $target, string $propertyName, mixed $value): void
    {
        $reflection = new ReflectionClass($target);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($target, $value);
    }

    /**
     * 指定したクラスの static 変数の値を取得する
     *
     * @param string $className クラス名（FQCN）
     * @param string $propertyName staticプロパティ名
     * @return mixed プロパティの値
     */
    public function getStaticProperty(string $className, string $propertyName)
    {
        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue();
    }

    /**
     * 指定したクラスの static 変数に値を設定する
     *
     * @param string $className クラス名（FQCN）
     * @param string $propertyName staticプロパティ名
     * @param mixed $value 設定する値
     */
    public function setStaticProperty(string $className, string $propertyName, mixed $value): void
    {
        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        // staticプロパティでない場合は例外にする
        if (! $property->isStatic()) {
            throw new \InvalidArgumentException("Property {$className}::\${$propertyName} is not static.");
        }

        $property->setValue(null, $value);
    }

    /**
     * クラスの定数を取得する
     *
     * @param string|object $objectOrClass
     * @param string $name
     * @return mixed
     */
    public function getConstrant(string|object $objectOrClass, string $name): mixed
    {
        $reflection = new ReflectionClass($objectOrClass);
        return $reflection->getConstant($name);
    }
}
