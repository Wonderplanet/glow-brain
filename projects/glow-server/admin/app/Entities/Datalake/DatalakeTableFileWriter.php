<?php

declare(strict_types=1);

namespace App\Entities\Datalake;

use App\Constants\DatalakeConstant;

class DatalakeTableFileWriter extends DatalakeFileWriter
{
    protected function generateFileName(): string
    {
        return sprintf(
            DatalakeConstant::FILE_NAME_FORMAT,
            $this->tableName,
            $this->targetDate->format('Ymd'),
            $this->part
        );
    }
}

