<?php

declare(strict_types=1);

namespace App\Entities\Datalake;

use App\Constants\DatalakeConstant;
use Carbon\CarbonImmutable;

class DatalakeColumnFileWriter extends DatalakeFileWriter
{
    protected string $columnName;

    public function __construct(
        string $tableName,
        string $columnName,
        CarbonImmutable $targetDate,
        string $disk,
        int $fileSizeLimit
    ) {
        $this->columnName = $columnName;
        parent::__construct($tableName, $targetDate, $disk, $fileSizeLimit);
    }

    protected function generateFileName(): string
    {
        return sprintf(
            DatalakeConstant::FILE_NAME_COLUMN_FORMAT,
            $this->tableName,
            $this->columnName,
            $this->targetDate->format('Ymd'),
            $this->part
        );
    }
}

