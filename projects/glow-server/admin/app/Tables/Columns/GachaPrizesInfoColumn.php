<?php

namespace App\Tables\Columns;

use App\Contracts\CsvExportable;
use App\Entities\RewardInfo;
use App\Models\Mst\OprGachaPrize;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Collection;

class GachaPrizesInfoColumn extends Column implements CsvExportable
{
    protected string $view = 'tables.columns.gacha-prizes-info-column';

    private const DEFAULT_MAX_PRIZES = 10;

    protected int $maxPrizes = self::DEFAULT_MAX_PRIZES; // デフォルト最大報酬数

    public function maxPrizes(int $count): static
    {
        $this->maxPrizes = $count;
        return $this;
    }

    public function getCsvHeaders(): array
    {
        $maxPrizes = $this->maxPrizes;
        
        $headers = [];
        for ($i = 1; $i <= $maxPrizes; $i++) {
            $headers[] = "prize_{$i}_id";
            $headers[] = "prize_{$i}_name";
            $headers[] = "prize_{$i}_amount";
        }
        return $headers;
    }

    public function getJapaneseCsvHeaders(): array
    {
        // 動的に最大報酬数を計算
        $maxPrizes = $this->maxPrizes;
        
        $headers = [];
        for ($i = 1; $i <= $maxPrizes; $i++) {
            $headers[] = "報酬{$i}ID";
            $headers[] = "報酬{$i}名前";
            $headers[] = "報酬{$i}個数";
        }
        return $headers;
    }

    public function getCsvData($record, array $context = []): array
    {
        // 直接レコードから報酬データを取得
        $prizeData = $this->extractPrizeDataFromRecord($record, $context);
        
        // 横並び形式
        $data = [];
        $maxPrizes = $this->maxPrizes;
        $addedPrizes = 0;

        if (!empty($prizeData)) {
            foreach ($prizeData as $prize) {
                $data[] = $prize['id'];
                $data[] = $prize['name'];
                $data[] = $prize['amount'];
                $addedPrizes++;
                
                if ($addedPrizes >= $maxPrizes) {
                    break;
                }
            }
        }

        // 不足分を空文字で埋める
        while ($addedPrizes < $maxPrizes) {
            $data[] = '';
            $data[] = '';
            $data[] = '';
            $addedPrizes++;
        }

        return $data;
    }

    /**
     * レコードから報酬データを抽出
     * 
     * @param array $context 追加のコンテキスト（例: 'prizeResources' => Collection）
     * @return array<array{id: mixed, name: string, amount: int}>
     *  - 'id': 報酬ID
     *  - 'name': 報酬名前
     *  - 'amount': 報酬個数
     */
    private function extractPrizeDataFromRecord($record, array $context = []): array
    {
        $gachaResult = $record?->log_gacha?->result;
        if (!$gachaResult) {
            return [];
        }

        try {
            $results = unserialize($gachaResult);
            if (!is_array($results)) {
                return [];
            }

            // 報酬リソース情報を取得または作成
            $prizeResources = $context['prizeResources'] ?? $this->createPrizeResourcesCollection($results);

            $prizeDataList = [];
            foreach ($results as $result) {
                if (isset($result['id'])) {
                    /** @var RewardInfo $prizeInfo */
                    $prizeInfo = $prizeResources->get($result['id']);
                    
                    if ($prizeInfo) {
                        $prizeDataList[] = [
                            'id' => $result['id'],
                            'name' => $prizeInfo->getName(),
                            'amount' => $prizeInfo->getAmount()
                        ];
                    } else {
                        // 報酬情報が見つからない場合は基本情報のみ
                        $prizeDataList[] = [
                            'id' => $result['id'] ?? '',
                            'name' => '名称不明',
                            'amount' => $result['resource_amount'] ?? 1
                        ];
                    }
                }
            }

            return $prizeDataList;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 報酬リソースコレクションを作成
     * 
     * @return Collection<string, RewardInfo>
     */
    private function createPrizeResourcesCollection(array $results): Collection
    {
        try {
            $ids = array_filter(array_map(fn($r) => $r['id'] ?? null, $results));
            if (empty($ids)) {
                return collect();
            }
            $oprGachaPrizes = OprGachaPrize::query()->whereIn('id', $ids)->get();
            
            $prizeResources = $oprGachaPrizes->map(function ($oprGachaPrize) {
                return $oprGachaPrize->prize_resource;
            })->filter();
            
            return $this->getRewardInfos($prizeResources);
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function supportsCsvExport(): bool
    {
        return true;
    }
}
