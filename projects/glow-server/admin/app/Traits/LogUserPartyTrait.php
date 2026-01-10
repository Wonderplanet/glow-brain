<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use App\Tables\Columns\UsrPartyUnitInfoColumn;

trait LogUserPartyTrait
{

    protected function getPartyInfoColumns(Collection $mstUnits): array
    {
        return [
            UsrPartyUnitInfoColumn::make('party_info')
                ->label('パーティ情報')
                ->getStateUsing(
                    function ($record) use ($mstUnits) {
                        $partyData = json_decode($record->party_units ?? '[]', true);
                        return $this->partyStatus($partyData, $mstUnits);
                    }
                )
                ->searchable()
                ->sortable(),
        ];
    }

    public function partyStatus(array $partyData, Collection $mstUnits)
    {
        $units = [];
        foreach ($partyData as $party)
        {
            $mstUnitId = $party['mst_unit_id'] ?? $party?->mst_unit_id ?? '';
            /** @var \App\Models\Mst\MstUnit|null $mstUnit */
            $mstUnit = $mstUnits->get($mstUnitId);

            if ($mstUnit === null) {
                continue;
            }

            $level = $party['level'] ?? $party?->level ?? '';
            $rank = $party['rank'] ?? $party?->rank ?? '';
            $gradeLevel = $party['grade_level'] ?? $party?->grade_level ?? '';

            $units[] = [
                'id' => $mstUnit->id ?? '',
                'name' => $mstUnit->mst_unit_i18n?->name ?? '',
                'level' => $level,
                'rank' => $rank,
                'gradeLevel' => $gradeLevel,
                'assetPath' => $mstUnit->makeAssetPath() ?? '',
                'bgPath' => $mstUnit->makeBgPath() ?? '',
            ];
        }

        return array_pad($units, 10, null);
    }

}
