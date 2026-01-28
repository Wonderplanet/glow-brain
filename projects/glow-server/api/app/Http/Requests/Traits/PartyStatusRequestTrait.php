<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

trait PartyStatusRequestTrait
{
    /**
     * @param string $base
     * @return array<string, string>
     */
    private function getPartyStatusRules(string $base): array
    {
        $fields = [
            'usrUnitId' => 'string',
            'mstUnitId' => 'string',
            'color' => 'string',
            'roleType' => 'string',
            'hp' => 'int',
            'atk' => 'int',
            'moveSpeed' => 'numeric',
            'summonCost' => 'int',
            'summonCoolTime' => 'int',
            'damageKnockBackCount' => 'int',
            'attackDelay' => 'int',
            'nextAttackInterval' => 'int',
        ];
        $notRequiredFields = [
            'specialAttackMstAttackId' => 'string|nullable',
            'mstUnitAbility1' => 'string|nullable',
            'mstUnitAbility2' => 'string|nullable',
            'mstUnitAbility3' => 'string|nullable',
        ];
        $rules = ["{$base}.partyStatus" => 'required|array'];
        foreach ($fields as $field => $type) {
            $rules["{$base}.partyStatus.*.{$field}"] = "required|{$type}";
        }
        foreach ($notRequiredFields as $field => $type) {
            $rules["{$base}.partyStatus.*.{$field}"] = $type;
        }
        return $rules;
    }
}
