<?php

namespace App\Livewire;

use Livewire\Component;

class UsrPartyUnitInfo extends Component
{
    public array $partyUnitInfo = [];

    protected $casts = [
        'partyUnitInfo' => 'array',
    ];

    public function mount($partyUnitInfo)
    {
        $this->partyUnitInfo = $partyUnitInfo;
    }

    public function render()
    {
        return view(
            'tables.columns.usr-party-unit-info-column',
            ['getState' => function() {
                return $this->partyUnitInfo;
            }]
        );
    }
}
