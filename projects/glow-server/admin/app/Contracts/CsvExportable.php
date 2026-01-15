<?php

namespace App\Contracts;

interface CsvExportable
{
    /**
     * CSVエクスポート用のヘッダーを取得
     *
     * @return array
     */
    public function getCsvHeaders(): array;

    /**
     * CSVエクスポート用の日本語ヘッダーを取得
     *
     * @return array
     */
    public function getJapaneseCsvHeaders(): array;

    /**
     * レコードからCSV用のデータを抽出
     *
     * @param mixed $record
     * @param array $context 追加のコンテキスト
     * @return array
     */
    public function getCsvData($record, array $context = []): array;

    /**
     * このカラムがCSVエクスポートをサポートするかどうか
     *
     * @return bool
     */
    public function supportsCsvExport(): bool;
}
