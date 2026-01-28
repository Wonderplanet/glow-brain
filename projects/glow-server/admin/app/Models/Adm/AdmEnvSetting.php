<?php

namespace App\Models\Adm;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmEnvSetting extends AdmModel
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'version',
        'client_version_hash',
        'env_status_string'
    ];

    public function getVersion(): string
    {
        return $this->version ?? '';
    }

    public function getClientVersionHash(): string
    {
        return $this->client_version_hash ?? '';
    }

    public function getEnvStatusString(): string
    {
        return $this->env_status_string ?? '';
    }
}
