<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Athenaクエリ結果からモデルを作成するためのインターフェース
 */
interface IAthenaModel
{
    /**
     * Athenaクエリ結果の配列からモデルインスタンスを作成する
     *
     * @param array $data Athenaクエリ結果の配列
     * @return static
     */
    public static function createFromAthenaArray(array $data): static;
}
