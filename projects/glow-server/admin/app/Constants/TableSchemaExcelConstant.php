<?php

namespace App\Constants;

use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TableSchemaExcelConstant
{
    public const float CM_TO_WIDTH = 5.02;
    public const float CM_TO_HEIGHT = 28.35;

    public const array FONT_STYLE_BOLD = [
        'bold' => true,
    ];

    public const array FILL_STYLE = [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFEF2CB',],
    ];

    public const array BORDER_STYLE_THIN = [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ];

    public const array CELL_STYLE_BORDER_FILL = [
        'font' => self::FONT_STYLE_BOLD,
        'fill' => self::FILL_STYLE,
        'borders' => self::BORDER_STYLE_THIN,
    ];

    public const array CELL_STYLE_BORDER = [
        'borders' => self::BORDER_STYLE_THIN,
    ];
}
