<?php

declare(strict_types=1);

namespace App\Entities\Athena;

/**
 * Athenaのクエリ結果を表すエンティティ
 */
class AthenaQueryResultEntity
{
    /**
     * @param array<int, string> $headers ヘッダー行
     * @param array<int, array<int, mixed>> $rows データ行
     * @param int $totalRows データ行の総数
     */
    public function __construct(
        public array $headers,
        public array $rows,
        public int $totalRows,
    ) {
    }

    /**
     * クエリ結果が空の場合のインスタンスを生成する
     * @return AthenaQueryResultEntity
     */
    public static function createEmpty(): self
    {
        return new self(
            headers: [],
            rows: [],
            totalRows: 0,
        );
    }

    /**
     * データ行を取得する
     *
     * @return array<int, array<int, mixed>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}
