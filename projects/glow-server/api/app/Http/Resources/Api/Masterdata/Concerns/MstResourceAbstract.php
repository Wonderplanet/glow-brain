<?php

namespace App\Http\Resources\Api\Masterdata\Concerns;

trait MstResourceAbstract
{
    /**
     * @var array<mixed>
     */
    protected static array $instances = [];

    /**
     * @return array<mixed>
     */
    public function getCasts(): array
    {
        return $this->cast;
    }

    /**
     * @return array<string>
     */
    public function getSnakeCased(): array
    {
        return $this->snakeCased;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $data = [];
        foreach (self::$instances as $instance) {
            $data[] = $instance->getValue();
        }
        return $data;
    }

    /**
     * @param array<mixed> $rows
     */
    public function build($rows): void
    {
        self::$instances = $this->createFromModels($rows);
    }
}
