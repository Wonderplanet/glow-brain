<?php

declare(strict_types=1);

namespace App\Entities\GachaSimulator;
/**
 * 抽選物ごとのシミュレーション結果を集計するエンティティ
 */
class GachaPrizeSimulationResultEntity
{
    public function __construct(
        private string $id,
        private string $resourceId,
        private string $resourceType,
        private string $itemName,
        private string $rarity,
        private float $provisionRate,
        private float $actualEmissionRate,
        private float $errorRate,
        private bool $rangeCheck,
        private int $emissionsNum,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getItemName(): string
    {
        return $this->itemName;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getProvisionRate(): float
    {
        return $this->provisionRate;
    }

    public function getActualEmissionRate(): float
    {
        return $this->actualEmissionRate;
    }

    public function getErrorRate(): float
    {
        return $this->errorRate;
    }

    /**
     * @return bool true: 誤差率エラーあり, false: 許容範囲内
     */
    public function isRangeCheckError(): bool
    {
        return $this->rangeCheck;
    }

    public function getEmissionsNum(): int
    {
        return $this->emissionsNum;
    }

    public function formatToArray(): array
    {
        return [
            'id' => $this->id,
            'resourceId' => $this->resourceId,
            'resourceType' => $this->resourceType,
            'itemName' => $this->itemName,
            'rarity' => $this->rarity,
            'provisionRate' => $this->provisionRate,
            'actualEmissionRate' => $this->actualEmissionRate,
            'errorRate' => $this->errorRate,
            'rangeCheck' => $this->rangeCheck,
            'emissionsNum' => $this->emissionsNum,
        ];
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['resourceId'],
            $data['resourceType'],
            $data['itemName'],
            $data['rarity'],
            $data['provisionRate'],
            $data['actualEmissionRate'],
            $data['errorRate'],
            $data['rangeCheck'],
            $data['emissionsNum'],
        );
    }
}
