<?php

namespace App\Exports;

use App\Constants\ContentsConstant;
use App\Constants\TableSchemaExcelConstant;
use App\Models\GenericModel;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;

class TableSchemaDocumentTablesSheetExport implements WithEvents, WithTitle
{
    public function __construct(
        private CarbonImmutable $executionTime,
        private GenericModel $genericModel,
        private string $dbType,
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $date = $this->executionTime->format('Y/m/d');

                // 列幅
                // 全体調整
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(2.5 * TableSchemaExcelConstant::CM_TO_WIDTH);
                }
                // テーブル名
                $sheet->getColumnDimension('E')->setWidth(10.0 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // カラム名
                $sheet->getColumnDimension('F')->setWidth(10.0 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // NULL許容
                $sheet->getColumnDimension('G')->setWidth(2.0 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // データ型
                $sheet->getColumnDimension('H')->setWidth(3.6 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // カラムコメント
                $sheet->getColumnDimension('I')->setWidth(14.5 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // 備考
                $sheet->getColumnDimension('J')->setWidth(14.5 * TableSchemaExcelConstant::CM_TO_WIDTH);

                // 行幅
                $sheet->getRowDimension(4)->setRowHeight(2.15 * TableSchemaExcelConstant::CM_TO_HEIGHT);

                // タイトル
                $sheet->setCellValue('D1', 'タイトル名')
                    ->getStyle('D1')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER_FILL);
                $sheet->setCellValue('E1', ContentsConstant::TITLE)
                    ->getStyle('E1')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER);

                // 作成日
                $sheet->setCellValue('D2', '作成日')
                    ->getStyle('D2')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER_FILL);
                $sheet->setCellValue('E2', $date)
                    ->getStyle('E2')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER);

                // ヘッダー行
                $headerColumns = range('B', 'J');
                $headerTitles = ['テーブルNo', 'カラムNo', 'DB名', 'テーブル名', 'カラム名', 'NULL許容', 'データ型', 'カラムコメント', '備考'];
                $headers = array_combine($headerColumns, $headerTitles);
                foreach ($headers as $col => $text) {
                    $sheet->setCellValue($col . '4', $text)
                        ->getStyle($col . '4')
                        ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER_FILL);
                }

                // テーブル情報
                $rowNum = 5;    // 5行目から
                $tableNum = 1;
                $dbName = $this->genericModel->getDBName();
                // データレイクと同じロジックでDB名をフィルタリング
                $dbName = StringUtil::filterDbName($this->dbType, $dbName);
                $className = get_class($this->genericModel);

                foreach ($this->genericModel->showTables() as $tableName) {
                    $model = (new $className())->setTableName($tableName);
                    $columns = $model->getColumns();
                    foreach ($columns as $column) {
                        $this->writeToSheetRow(
                            $sheet,
                            $rowNum,
                            "{$this->dbType}_{$tableNum}",
                            $column->ordinal_position,
                            $dbName,
                            $tableName,
                            $column->column_name,
                            $column->is_nullable,
                            $this->filterColumnType($column->data_type, $column->column_type),
                            $column->column_comment,
                            $this->filterRemarks($column->data_type, $column->column_type),
                        );
                        $rowNum++;
                    }
                    $tableNum++;
                }
            }
        ];
    }

    private function filterColumnType(string $dataType, string $columnType): string
    {
        // enum型の場合、column_typeにenum('a','b','c')のように格納されているので、enumだけにする
        if ($dataType === 'enum') {
            return $dataType;
        }
        return $columnType;
    }

    private function filterRemarks(string $dataType, string $columnType): string
    {
        // enum型の場合、column_typeにenum('a','b','c')の内容を備考に記載
        if ($dataType === 'enum') {
            if (preg_match("/^enum\((.*)\)$/", $columnType, $matches)) {
                $values = str_getcsv($matches[1], ',', "'");
                return implode(',', $values);
            }
        }
        return '';
    }

    private function writeToSheetRow(
        Sheet $sheet,
        int $rowNum,
        string $tableNo,
        int $columnNo,
        string $dbName,
        string $tableName,
        string $columnName,
        string $nullable,
        string $columnType,
        string $columnComment,
        string $remarks,
    ): void {
        $cellValues = [
            'B' => $tableNo,
            'C' => $columnNo,
            'D' => $dbName,
            'E' => $tableName,
            'F' => $columnName,
            'G' => $nullable,
            'H' => $columnType,
            'I' => $columnComment,
            'J' => $remarks,
        ];
        foreach ($cellValues as $col => $value) {
            $sheet->setCellValue("{$col}{$rowNum}", $value)
                ->getStyle("{$col}{$rowNum}")
                ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER);
        }
    }

    public function title(): string
    {
        return "{$this->dbType}テーブル定義";
    }
}
