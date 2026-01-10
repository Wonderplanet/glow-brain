<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RewardInfoTableColumn extends Component
{
    public array $columns = [];
    public array $rows = [];

    public function __construct(array $columns, array $rows)
    {
        $this->columns = $columns;
        $this->rows = $rows;
    }

    public function render(): View|Closure|string
    {
        return view('components.reward-info-table-column');
    }
}
