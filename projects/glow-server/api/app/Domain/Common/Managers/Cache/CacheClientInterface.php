<?php

declare(strict_types=1);

namespace App\Domain\Common\Managers\Cache;

interface CacheClientInterface
{
    /**
     * sorted setにメンバーを追加
     * @param string $key
     * @param array<string, int> $members
     * @return int
     */
    public function zAdd(string $key, array $members): int;

    /**
     * sorted setのメンバーのスコアを加算
     * @param string $key
     * @param int    $increment
     * @param string $member
     * @return float
     */
    public function zIncrBy(string $key, int $increment, string $member): float;

    /**
     * sorted setからメンバーを削除
     * @param string $key
     * @param array<string>  $members
     * @return int
     */
    public function zRem(string $key, array $members): int;

    /**
     * sorted setのスコアの範囲内のメンバー数を取得
     * @param string       $key
     * @param int | string $start string: '-inf' or '+inf'
     * @param int | string $end string: '-inf' or '+inf'
     * @return int
     */
    public function zCount(string $key, int|string $start, int|string $end): int;

    /**
     * sorted setのスコアを取得
     * @param string $key
     * @param string $member
     * @return float | bool スコアが存在しない場合にfalseを返す
     */
    public function zScore(string $key, string $member): float|bool;

    /**
     * sorted setの範囲内のメンバーを降順に取得
     * @param string      $key
     * @param int| string $start string: '-inf' or '+inf'
     * @param int| string $end string: '-inf' or '+inf'
     * @param bool        $withScores
     * @return array<string, float>
     */
    public function zRevRange(string $key, int|string $start, int|string $end, bool $withScores): array;

    /**
     * sorted setの降順にソートされたスコアの範囲内のメンバーを取得
     * @param string       $key
     * @param int | string $max string: '-inf' or '+inf'
     * @param int | string $min string: '-inf' or '+inf'
     * @param bool        $withScores
     * @return array<string, float>|false
     */
    public function zRevRangeByScore(
        string $key,
        int|string $max,
        int|string $min,
        bool $withScores,
        int $limit = 0
    ): array|false;

    /**
     * sorted setの和集合を取得
     * @param string $destination
     * @param array<string> $keys
     * @param array<int> $weights
     * @param string $aggregateFunction
     * @return int
     */
    public function zUnionStore(string $destination, array $keys, array $weights, string $aggregateFunction): int;

    /**
     * キャッシュの有効期限を設定
     * @param string $key
     * @param int    $ttl
     * @return void
     */
    public function expire(string $key, int $ttl): void;

    /**
     * キャッシュの有効期限(秒)を取得
     * @param string $key
     * @return int -1:無制限 -2:キーが存在しない
     */
    public function ttl(string $key): int;

    /**
     * キャッシュを設定する
     * @param string   $key
     * @param mixed    $value
     * @param int|null $ttl
     * @return void
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void;

    /**
     * キーが存在しない場合にのみキャッシュを設定する(排他制御用)
     * @param string   $key
     * @param mixed    $value
     * @param int|null $ttl
     * @return bool
     */
    public function setIfNotExists(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * キャッシュを取得する
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * キャッシュの値をインクリメント
     * @param string $key
     * @param int    $increment
     * @return int
     */
    public function incrBy(string $key, int $increment): int;

    /**
     * 対象のキーが存在するか
     * @param string $key
     */
    public function exists(string $key): bool;

    /**
     * キャッシュを削除する
     * @param string $key
     * @return void
     */
    public function del(string $key): void;
}
