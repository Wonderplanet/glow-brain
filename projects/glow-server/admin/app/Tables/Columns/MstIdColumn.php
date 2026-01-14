<?php

namespace App\Tables\Columns;

use App\Models\Mst\IAssetImage;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MstIdColumn extends Column
{
    public mixed $getMstIdCallback = null;
    public mixed $getMstNameCallback = null;
    public mixed $getMstDetailPageUrlCallback = null;

    public mixed $getMstCallback = null;

    protected string $view = 'tables.columns.mst-id-column';

    public function getMstUsing(callable $getMstCallback): self
    {
        $this->getMstCallback = $getMstCallback;
        return $this;
    }

    public function getMst($record): mixed
    {
        if ($this->getMstCallback === null) {
            $this->getMstCallback = function ($record) {
                return $record;
            };
        }

        return ($this->getMstCallback)($record);
    }

    public function getMstIdUsing(callable $getMstIdCallback): self
    {
        $this->getMstIdCallback = $getMstIdCallback;
        return $this;
    }

    public function getMstId($record): string
    {
        if ($this->getMstIdCallback === null) {
            $this->getMstIdCallback = function ($record) {
                return $this->getMst($record)?->id ?? '';
            };
        }

        return ($this->getMstIdCallback)($record);
    }

    public function getMstDataNameUsing(callable $getMstNameCallback): self
    {
        $this->getMstNameCallback = $getMstNameCallback;
        return $this;
    }

    public function getMstDataName($record): string
    {
        if ($this->getMstNameCallback === null) {
            $mst = $this->getMst($record);
            if (!($mst instanceof Model)) {
                return '';
            }
            $singularTableName = Str::singular($mst->getTable());
            $path = sprintf(
                '%s.%s_i18n.name',
                $singularTableName,
                $singularTableName,
            );
            $this->getMstNameCallback = function ($record) use ($path) {
                return data_get(
                    $record,
                    $path,
                    '',
                );
            };
        }

        return ($this->getMstNameCallback)($record);
    }

    public function getMstDetailPageUrlUsing(callable $getMstDetailPageUrlCallback): self
    {
        $this->getMstDetailPageUrlCallback = $getMstDetailPageUrlCallback;
        return $this;
    }

    public function getMstDetailPageUrl($record): string
    {
        if ($this->getMstDetailPageUrlCallback === null) {
            return '';
        }

        return ($this->getMstDetailPageUrlCallback)($record);
    }

    public function getAssetPath($record): ?string
    {
        $record = $this->getMst($record);

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeAssetPath();
    }

    public function getBgPath($record): ?string
    {
        $record = $this->getMst($record);

        if (!($record instanceof IAssetImage)) {
            return null;
        }

        return $record->makeBgPath();
    }
}
