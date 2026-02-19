<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DescriptionList extends Component
{
    public string $title;
    public array $list;

    public function __construct(array $list, string $title = '')
    {
        $this->title = $title;
        $this->list = $list;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.description-list');
    }
}
