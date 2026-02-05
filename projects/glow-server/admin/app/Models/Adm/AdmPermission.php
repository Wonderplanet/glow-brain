<?php

namespace App\Models\Adm;

use App\Models\Adm\AdmPermissionFeature;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;

class AdmPermission extends Permission
{
    protected $table = 'adm_permissions';
}
