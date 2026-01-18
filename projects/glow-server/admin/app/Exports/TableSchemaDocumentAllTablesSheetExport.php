<?php

namespace App\Exports;

use App\Constants\ContentsConstant;
use App\Constants\TableSchemaExcelConstant;
use App\Models\GenericAdmModel;
use App\Models\GenericLogModel;
use App\Models\GenericModel;
use App\Models\GenericMstModel;
use App\Models\GenericOprModel;
use App\Models\GenericUsrModel;
use App\Utils\StringUtil;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;

class TableSchemaDocumentAllTablesSheetExport implements WithEvents, WithTitle
{
    public function __construct(
        private CarbonImmutable $executionTime,
    ) {}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $date = $this->executionTime->format('Y/m/d');

                // 列幅
                // 全体調整
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(2.5 * TableSchemaExcelConstant::CM_TO_WIDTH);
                }
                // テーブル名
                $sheet->getColumnDimension('D')->setWidth(10.0 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // テーブルコメント
                $sheet->getColumnDimension('E')->setWidth(14.5 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // 備考
                $sheet->getColumnDimension('F')->setWidth(14.5 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // 作成日
                $sheet->getColumnDimension('G')->setWidth(3.6 * TableSchemaExcelConstant::CM_TO_WIDTH);
                // 更新日
                $sheet->getColumnDimension('H')->setWidth(3.6 * TableSchemaExcelConstant::CM_TO_WIDTH);

                // 行幅
                $sheet->getRowDimension(4)->setRowHeight(2.15 * TableSchemaExcelConstant::CM_TO_HEIGHT);

                // タイトル
                $sheet->setCellValue('C1', 'タイトル名')
                    ->getStyle('C1')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER_FILL);
                $sheet->setCellValue('D1', ContentsConstant::TITLE)
                    ->getStyle('D1')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER);

                // 作成日
                $sheet->setCellValue('C2', '作成日')
                    ->getStyle('C2')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER_FILL);
                $sheet->setCellValue('D2', $date)
                    ->getStyle('D2')
                    ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER);

                // ヘッダー行
                $headerColumns = range('B', 'H');
                $headerTitles = ['No', 'DB名', 'テーブル名', 'テーブルコメント', '備考', '作成日', '更新日'];
                $headers = array_combine($headerColumns, $headerTitles);
                foreach ($headers as $col => $text) {
                    $sheet->setCellValue($col . '4', $text)
                        ->getStyle($col . '4')
                        ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER_FILL);
                }

                // テーブル情報
                $genericModelList = [
                    'mst' => new GenericMstModel(),
                    'opr' => new GenericOprModel(),
                    'usr' => new GenericUsrModel(),
                    'log' => new GenericLogModel(),
                    'adm' => new GenericAdmModel(),
                ];
                $rowNum = 5;    // 5行目から
                /** @var GenericModel $genericModel */
                foreach ($genericModelList as $type => $genericModel) {
                    $tableNum = 1;
                    $dbName = $genericModel->getDBName();
                    // データレイクと同じロジックでDB名をフィルタリング
                    $dbName = StringUtil::filterDbName($type, $dbName);
                    $className = get_class($genericModel);

                    // Jsonカラムはdlテーブルとして分割する
                    $dls = collect();
                    foreach ($genericModel->showTables() as $tableName) {
                        $model = (new $className())->setTableName($tableName);
                        $meta = $model->getTableMeta();
                        $this->writeToSheetRow(
                            $sheet,
                            $rowNum,
                            "{$type}_{$tableNum}",
                            $dbName,
                            $tableName,
                            $meta['comment'] ?? '',
                            $meta['remarks'] ?? '',
                            $meta['created_at'] ?? '',
                            $meta['updated_at'] ?? ''
                        );

                        $columns = $model->getColumns();
                        foreach ($columns as $column) {
                            if ($column->data_type === 'json') {
                                // 20250516 一旦dl出力をやめる
                                //$dls->add("{$tableName}___{$column->column_name}");
                            }
                        }

                        $rowNum++;
                        $tableNum++;
                    }

                    // dlテーブルの情報を出力
                    $tableNum = 1;
                    foreach ($dls as $dlItem) {
                        [$tableName, $columnName] = explode('___', $dlItem);
                        $this->writeToSheetRow(
                            $sheet,
                            $rowNum,
                            "dl_{$type}_{$tableNum}",
                            $dbName,
                            "dl_{$tableName}_{$columnName}",
                            '',
                            "{$tableName}の{$columnName}カラムのJson内容",
                            '',
                            ''
                        );
                        $rowNum++;
                        $tableNum++;
                    }
                }
            }
        ];
    }

    private function writeToSheetRow(
        Sheet $sheet,
        int $rowNum,
        string $no,
        string $dbName,
        string $tableName,
        string $tableComment,
        string $remarks,
        string $createdAt,
        string $updatedAt
    ): void {
        $cellValues = [
            'B' => $no,
            'C' => $dbName,
            'D' => $tableName,
            'E' => $tableComment,
            'F' => $remarks,
            'G' => $createdAt,
            'H' => $updatedAt,
        ];
        foreach ($cellValues as $col => $value) {
            $sheet->setCellValue("{$col}{$rowNum}", $value)
                ->getStyle("{$col}{$rowNum}")
                ->applyFromArray(TableSchemaExcelConstant::CELL_STYLE_BORDER);
        }
    }

    public function title(): string
    {
        return 'テーブル一覧';
    }
}
