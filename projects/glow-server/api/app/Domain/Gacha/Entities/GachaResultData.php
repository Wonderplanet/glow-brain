<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use Illuminate\Support\Collection;

readonly class GachaResultData
{
    /**
     * @param array<string> $prizeTypes
     */
    public function __construct(
        private string $oprGachaId,
        private Collection $result,
        private array $prizeTypes,
    ) {
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    /**
     * @return array<string>
     */
    public function getPrizeTypes(): array
    {
        return $this->prizeTypes;
    }

    /**
     * @return Collection<\App\Domain\Gacha\Entities\GachaBoxInterface>
     */
    public function getResult(): Collection
    {
        return $this->result;
    }

    /**
     * usr_tutorial_gachas.gacha_result_jsonに保存するための配列化
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'opr_gacha_id' => $this->oprGachaId,
            'result' => $this->result->map(fn (GachaPrize $prize) => $prize->formatToLog())->toArray(),
            'prize_types' => $this->prizeTypes,
        ];
    }

    /**
     * usr_tutorial_gachas.gacha_result_jsonに保存したデータから復元
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['opr_gacha_id'],
            collect((array)$data['result'])->map(function (array $item) {
                return GachaPrize::createFromArray($item);
            }),
            $data['prize_types'],
        );
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return $this->getResult()->map(function ($item, $index) {
            return array_merge(
                $item->formatToLog(),
                [
                    'opr_gacha_id' => $this->oprGachaId,
                    'index' => $index,
                    'prize_type' => $this->prizeTypes[$index] ?? '',
                ],
            );
        })->toArray();
    }
}
