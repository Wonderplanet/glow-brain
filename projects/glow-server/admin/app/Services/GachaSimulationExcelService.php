<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Gacha\Enums\GachaPrizeType;
use App\Domain\Gacha\Services\GachaService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GachaSimulationExcelService
{
    protected GachaService $gachaService;
    protected GachaSimulatorService $gachaSimulatorService;
    public function __construct(
    ) {
        $this->gachaService = app()->make(GachaService::class);
        $this->gachaSimulatorService = app()->make(GachaSimulatorService::class);
    }

    public function downloadGachaResults(
        string $oprGachaId,
        string $gachaTypeLabel,
        CarbonImmutable $simulationDate,
        array $results,
        string $gachaName,
        GachaPrizeType $prizeType,
    ): StreamedResponse {

        // Excelファイルを作成
        $spreadsheet = $this->createSpreadsheet(
            $oprGachaId,
            $gachaTypeLabel,
            $simulationDate,
            $results,
        );

        // レスポンスでExcelファイルをダウンロード
        return Response::streamDownload(
            callback: $this->getCallback($spreadsheet),
            name: $this->getFileName($oprGachaId, $gachaName, $simulationDate, $prizeType->value),
            headers: $this->getHeaders(),
        );
    }

    private function createSpreadsheet(
        string $oprGachaId,
        string $gachaTypeLabel,
        CarbonImmutable $simulationDate,
        array $results
    ): \PhpOffice\PhpSpreadsheet\Spreadsheet {
        // テンプレートファイルを読み込む
        $templatePath = resource_path('views/filament/templates/gacha_result_template.xlsx');
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found: {$templatePath}");
        }

        // PhpSpreadsheetを使用してExcelファイルを操作
        $spreadsheet = IOFactory::createReader('Xlsx')->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 集計情報を追加
        $sheet->setCellValueExplicit('B2', $oprGachaId, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', $gachaTypeLabel);
        $sheet->setCellValue('B4', $simulationDate->format('Y/m/d'));
        $sheet->setCellValue('G4', '=SUM(G7:G' . (6 + count($results)) . ')');

        // A7からデータを順に書き込み
        $row = 7;
        $styleOrigin = $sheet->getStyle("A{$row}:G{$row}");
        $width = $sheet->getColumnDimension('A')->getWidth();
        $height = $sheet->getRowDimension($row)->getRowHeight();

        foreach ($results as $result) {
            $sheet->setCellValue("A{$row}", $row - 6);
            $sheet->setCellValue("B{$row}", $result['resourceId']);
            $sheet->setCellValue("C{$row}", $result['itemName']);
            $sheet->setCellValue("D{$row}", $result['provisionRate']);
            $sheet->setCellValue("E{$row}", $result['actualEmissionRate']);
            $sheet->setCellValue("F{$row}", "=ROUNDDOWN(((E{$row} - D{$row}) / D{$row})*100, 5)");
            $sheet->setCellValue("G{$row}", $result['emissionsNum']);

            // スタイルを複製して適用
            $style = clone $styleOrigin;

            $sheet->duplicateStyle($style, "A{$row}:G{$row}");
            $sheet->getColumnDimension('A')->setWidth($width);
            $sheet->getRowDimension($row)->setRowHeight($height);

            $row++;
        }

        return $spreadsheet;
    }


    private function getCallback(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): callable
    {
        return function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };
    }

    private function getFileName(
        string $oprGachaId,
        string $gachaName,
        CarbonImmutable $simulationDate,
        string $suffix,
    ): string {
        return "GLOW_{$oprGachaId}_{$gachaName}_{$simulationDate->format('Ymd_His')}_{$suffix}.xlsx";
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }
}
