<?php

namespace App\Exports;

use App\Models\GenericAdmModel;
use App\Models\GenericLogModel;
use App\Models\GenericMstModel;
use App\Models\GenericOprModel;
use App\Models\GenericUsrModel;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TableSchemaDocumentExport implements WithMultipleSheets
{
    public function __construct(
        private CarbonImmutable $executionTime,
    ) {}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function sheets(): array
    {
        return [
            new TableSchemaDocumentAllTablesSheetExport($this->executionTime),
            new TableSchemaDocumentTablesSheetExport($this->executionTime, new GenericMstModel(), 'mst'),
            new TableSchemaDocumentTablesSheetExport($this->executionTime, new GenericOprModel(), 'opr'),
            new TableSchemaDocumentTablesSheetExport($this->executionTime, new GenericUsrModel(), 'usr'),
            new TableSchemaDocumentTablesSheetExport($this->executionTime, new GenericLogModel(), 'log'),
            new TableSchemaDocumentTablesSheetExport($this->executionTime, new GenericAdmModel(), 'adm'),
        ];
    }
}
