<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\CsvExportable;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Column;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomCsvDownloadAction extends Action
{
    protected $fileName;
    protected array $context = [];

    public static function make(?string $name = 'customDownloadCsv'): static
    {
        return parent::make($name)
            ->label('CSVダウンロード')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success');
    }

    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function withContext(array $context): static
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function ($livewire) {
            return $this->downloadCsv($livewire);
        });
    }

    protected function downloadCsv($livewire): StreamedResponse
    {
        $query = $livewire->getFilteredTableQuery();
        $columns = $livewire->getTable()->getColumns();
        
        return response()->stream(function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            
            // ヘッダーを書き込む
            $this->writeHeaders($handle, $columns);
            
            // ボディを書き込む
            $query->chunkById(1000, function ($records) use ($handle, $columns) {
                foreach ($records as $record) {
                    $row = $this->buildRow($record, $columns);
                    fputcsv($handle, $row);
                }
                unset($records);
            });
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . ($this->fileName ?: 'export') . '_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ]);
    }

    private function writeHeaders($handle, array $columns): void
    {
        $headers = [];
        $japaneseHeaders = [];
        
        foreach ($columns as $column) {
            if ($column instanceof CsvExportable && $column->supportsCsvExport()) {
                // カラム自身にヘッダーの取得を委譲
                $headers = array_merge($headers, $column->getCsvHeaders());
                $japaneseHeaders = array_merge($japaneseHeaders, $column->getJapaneseCsvHeaders());
            } elseif ($column instanceof Column) {
                // 通常のカラムの場合
                $headers[] = $column->getName();
                $japaneseHeaders[] = $column->getLabel() ?? $column->getName();
            }
        }
        
        fputcsv($handle, $headers);
        fputcsv($handle, $japaneseHeaders);
    }

    private function buildRow($record, array $columns): array
    {
        $row = [];
        
        foreach ($columns as $column) {
            if ($column instanceof CsvExportable && $column->supportsCsvExport()) {
                // カラム自身にデータ取得を委譲
                $row = array_merge($row, $column->getCsvData($record, $this->context));
            } elseif ($column instanceof Column) {
                // 通常のカラムの場合
                $row[] = $this->getColumnValue($record, $column);
            }
        }
        
        return $row;
    }

    private function getColumnValue($record, Column $column)
    {
        try {
            $state = data_get($record, $column->getName());
            $displayValue = $column->formatState($state);
            
            return is_string($displayValue) ? strip_tags($displayValue) : ($displayValue ?? '');
        } catch (\Exception $e) {
            return data_get($record, $column->getName()) ?? '';
        }
    }
}
