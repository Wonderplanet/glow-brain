<?php

namespace App\Models\Adm;

use App\Constants\Database;
use Illuminate\Database\Eloquent\Concerns\HasUuids as BaseHasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmInGameNotice extends Model
{
    use BaseHasUuids;
    use HasFactory;

    protected $connection = Database::ADMIN_CONNECTION;

    public function author()
    {
        return $this->hasOne(AdmUser::class, 'id', 'author_adm_user_id');
    }

    public function approver()
    {
        return $this->hasOne(AdmUser::class, 'id', 'approval_adm_user_id');
    }
}
