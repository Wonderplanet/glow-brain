<?php

declare(strict_types=1);

namespace App\Domain\Common\Managers\Cache;

use Momento\Auth\CredentialProvider;
use Momento\Cache\CacheClient;
use Momento\Config\Configurations\Laptop;
use Momento\Logging\StderrLoggerFactory;

class MomentoCacheClient implements CacheClientInterface
{
    private \Momento\Cache\MomentoCacheClient $momentoCacheClient;

    public function __construct()
    {
        // FIXME タイムアウトが発生するのでチャンネル数変更の対応を対応前のコードに戻してみる
//        $authProvider = CredentialProvider::fromEnvironmentVariable("MOMENTO_API_KEY");
//        $configuration = InRegion::latest(new StderrLoggerFactory());
//        $newGrpcConfig = $configuration->getTransportStrategy()
//            ->getGrpcConfig()
//            ->withDeadlineMilliseconds(1500)
//            ->withNumGrpcChannels(5)
//            ->withForceNewChannel(true);
//        $configuration = $configuration->withTransportStrategy(
//            $configuration->getTransportStrategy()->withGrpcConfig($newGrpcConfig)
//        );
        $authProvider = CredentialProvider::fromEnvironmentVariable('MOMENTO_API_KEY');
        $configuration = Laptop::latest(new StderrLoggerFactory());
        // MomentoはTTL無制限がないのでデフォルトのTTLを5年としておく
        $itemDefaultTtlSeconds = 60 * 60 * 24 * 365 * 5;
        $client = new CacheClient($configuration, $authProvider, $itemDefaultTtlSeconds);
        $this->momentoCacheClient = new \Momento\Cache\MomentoCacheClient($client, env('MOMENTO_CACHE_NAME'));
    }

    /**
     * MomentoのzAddに渡すための引数を準備する
     * @param array<string, float> $members
     * @return array{float, array<int, string|float>}
     */
    private static function prepareMomentoZAddArguments(array $members): array
    {
        // 最初のメンバーとスコアを取得
        $firstMember = array_key_first($members);
        $firstScore = $members[$firstMember];

        // 残りのメンバーとスコアを配列にまとめる
        $more_scores_and_mems = [$firstMember];
        $isFirst = true;

        foreach ($members as $member => $score) {
            if ($isFirst) {
                $isFirst = false;
                continue; // 最初の要素をスキップ
            }

            $more_scores_and_mems[] = (float) $score;
            $more_scores_and_mems[] = $member;
        }

        return [(float) $firstScore, $more_scores_and_mems];
    }

    /**
     * @inheritDoc
     */
    public function zAdd(string $key, array $members): int
    {
        if ($members === []) {
            return 0;
        }

        [$firstScore, $more_scores_and_mems] = $this->prepareMomentoZAddArguments($members);

        return $this->momentoCacheClient->zAdd($key, $firstScore, ...$more_scores_and_mems);
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, int $increment, string $member): float
    {
        return $this->momentoCacheClient->zIncrBy($key, (float) $increment, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRem(string $key, array $members): int
    {
        return $this->momentoCacheClient->zRem($key, $members);
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int|string $start, int|string $end): int
    {
        return $this->momentoCacheClient->zCount($key, $start, $end);
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member): float|bool
    {
        return $this->momentoCacheClient->zScore($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int|string $start, int|string $end, bool $withScores): array
    {
        return $this->momentoCacheClient->zRevRange($key, $start, $end, $withScores);
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScore(
        string $key,
        int|string $max,
        int|string $min,
        bool $withScores,
        int $limit = 0
    ): array|false {
        $option['withscores'] = $withScores;
        if ($limit) {
            $option['limit'] = [0, $limit];
        }
        return $this->momentoCacheClient->zRevRangeByScore(
            $key,
            (string) $max,
            (string) $min,
            $option
        );
    }

    /**
     * @inheritDoc
     */
    public function zUnionStore(string $destination, array $keys, array $weights, string $aggregateFunction): int
    {
        $result = $this->momentoCacheClient->exists(...$keys);
        if ($result === 0) {
            // 結合対象のキーがすべて存在しない場合はzunionstoreが失敗するので0を返す
            return 0;
        }
        return $this->momentoCacheClient->zunionstore($destination, $keys, $weights, $aggregateFunction);
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl): void
    {
        $this->momentoCacheClient->expire($key, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key): int
    {
        return $this->momentoCacheClient->ttl($key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $option = null;
        if (!is_null($ttl)) {
            $option = ['ex' => $ttl];
        }
        $this->momentoCacheClient->set($key, serialize($value), $option);
    }

    /**
     * @inheritDoc
     */
    public function setIfNotExists(string $key, mixed $value, ?int $ttl = null): bool
    {
        $option = ['nx'];
        if (!is_null($ttl)) {
            $option['ex'] = $ttl;
        }
        return $this->momentoCacheClient->set($key, serialize($value), $option) !== false;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $value = $this->momentoCacheClient->get($key);
        if ($value === null || $value === false) {
            return null;
        }
        try {
            return unserialize($value);
        } catch (\Throwable $e) {
            // シリアライズされていない場合はそのまま返す
            // 主にincrByなどsetを通さない(serializeなし)で値をセットしてgetで取得するときなど
            return $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $increment): int
    {
        return $this->momentoCacheClient->incrBy($key, $increment);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $key): bool
    {
        return (bool) $this->momentoCacheClient->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function del(string $key): void
    {
        $this->momentoCacheClient->del($key);
    }
}
