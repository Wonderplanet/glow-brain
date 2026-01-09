<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    public string $title = '';

    /**
     * テーブルデータを格納した配列
     * key: カラム名, value: カラムデータ
     * @var array<string, mixed>
     */
    public array $rows = [];

    public array $columns = [];

    public function __construct(
        string $title,
        array $rows,
    ) {
        $this->title = $title;
        $this->rows = $rows;

        if (!empty($rows)) {
            $this->columns = array_keys($rows[0]);
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.table');
    }
}
