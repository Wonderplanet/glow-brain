<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;

/**
 * TipTapEditorを使って入力されたマークアップ文字列をレンダリングするカラム
 */
class TipTapHtmlColumn extends Column
{
    protected string $view = 'tables.columns.tip-tap-html-column';
}
