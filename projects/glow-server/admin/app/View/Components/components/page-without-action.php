<?php

namespace App\View\Components\components;

use Closure;
use Illuminate\Contracts\View\View;

class page-without-action extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.components.page-without-action');
    }
}
