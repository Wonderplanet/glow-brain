<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Column;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleCsvDownloadAction extends Action
{
    protected $fileName;

    public static function make(?string $name = 'downloadCsv'): static
    {
        return parent::make($name)
            ->label('CSVダウンロード')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray');
    }

    /**
     * ファイル名を設定
     */
    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * アクション実行時の処理を設定
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function ($livewire) {
            return $this->downloadCsv($livewire);
        });
    }

    /**
     * CSV形式でデータをダウンロード
     */
    protected function downloadCsv($livewire): StreamedResponse
    {
        // ファイル名を自動取得する試行
        $fileName = $this->fileName;
        
        if (!$fileName) {
            // 複数の方法でファイル名を生成
            $fileName = $this->generateFileNameFromLivewire($livewire);
        }
        
        $fileName = $fileName ?: 'export';
        $fullFileName = "{$fileName}_" . now()->format('Y-m-d_H-i-s') . '.csv';

        // フィルター適用済みの全データを取得
        $query = $livewire->getFilteredTableQuery();
        
        // テーブルカラムの設定を取得
        $columns = $livewire->getTable()->getColumns();
        $columnMap = $this->buildColumnMap($columns);

        return response()->streamDownload(function () use ($query, $columns, $columnMap) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM を追加（Excelで正しく表示するため）
            fwrite($handle, "\xEF\xBB\xBF");
            
            $headerWritten = false;

            // データを取得してCSVに出力
            $query->orderBy('created_at', 'desc')->chunk(1000, function ($records) use ($handle, &$headerWritten, $columns, $columnMap) {
                foreach ($records as $record) {
                    // Filamentで既に変換済みのデータを取得
                    $data = [];
                    foreach ($columns as $column) {
                        if ($column instanceof Column) {
                            $columnName = $column->getName();
                            // 変換済みの表示値を取得
                            try {
                                $state = data_get($record, $columnName);
                                $displayValue = $column->formatState($state);
                            } catch (\Exception $e) {
                                // フォーマットに失敗した場合は元の値を使用
                                $displayValue = data_get($record, $columnName);
                            }
                            // HTMLタグを除去（文字列の場合のみ）
                            if (is_string($displayValue)) {
                                $data[$columnName] = strip_tags($displayValue);
                            } else {
                                $data[$columnName] = $displayValue ?? '';
                            }
                        }
                    }
                    
                    // 最初のレコードでヘッダーを書き込み
                    if (!$headerWritten) {
                        // 1行目: カラム名
                        fputcsv($handle, array_keys($data));
                        
                        // 2行目: ラベル（ない場合は空白）
                        $labels = $this->buildCsvLabels($data, $columnMap);
                        fputcsv($handle, $labels);
                        
                        $headerWritten = true;
                    }
                    
                    // データ行を書き込み
                    fputcsv($handle, array_values($data));
                }
            });

            fclose($handle);
        }, $fullFileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fullFileName . '"',
        ]);
    }

    /**
     * テーブルカラムからカラム名とラベルのマップを構築
     */
    protected function buildColumnMap(array $columns): array
    {
        $columnMap = [];
        
        foreach ($columns as $column) {
            if ($column instanceof Column) {
                $columnName = $column->getName();
                $label = $column->getLabel();
                
                // ラベルが設定されている場合はそれを使用、なければカラム名をそのまま使用
                $columnMap[$columnName] = $label ?: $columnName;
            }
        }
        
        return $columnMap;
    }

    /**
     * CSVヘッダーを構築（テーブルラベルを優先）
     */
    protected function buildCsvHeaders(array $data, array $columnMap): array
    {
        $headers = [];
        
        foreach (array_keys($data) as $columnName) {
            // テーブルに定義されているカラムのラベルを使用、なければそのままカラム名
            $headers[] = $columnMap[$columnName] ?? $columnName;
        }
        
        return $headers;
    }

    /**
     * CSVラベル行を構築（ラベルがない場合は空白）
     */
    protected function buildCsvLabels(array $data, array $columnMap): array
    {
        $labels = [];
        
        foreach (array_keys($data) as $columnName) {
            // テーブルに定義されているカラムのラベルを使用、なければ空白
            $label = $columnMap[$columnName] ?? '';
            $labels[] = ($label !== $columnName) ? $label : '';
        }
        
        return $labels;
    }

    /**
     * Livewireオブジェクトからファイル名を生成
     */
    protected function generateFileNameFromLivewire($livewire): ?string
    {
        // 方法1: モデルクラス名から取得
        if (method_exists($livewire, 'getModel')) {
            $model = $livewire->getModel();
            if ($model) {
                return (new $model)->getTable();
            }
        }
        
        // 方法2: クエリからテーブル名を取得
        try {
            $query = $livewire->getFilteredTableQuery();
            if ($query && method_exists($query, 'getModel')) {
                $model = $query->getModel();
                if ($model) {
                    return $model->getTable();
                }
            }
        } catch (\Exception $e) {
            // エラーは無視
        }
        
        // 方法3: Livewireクラス名から推測
        $className = class_basename(get_class($livewire));
        if (preg_match('/^(.+?)(Page|Resource)$/', $className, $matches)) {
            return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $matches[1]));
        }
        
        return null;
    }
}
