<?php

namespace App\Models\Adm;

use App\Constants\Database;
use Illuminate\Database\Eloquent\Concerns\HasUuids as BaseHasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property Carbon created_at
 * @property Carbon updated_at
 */
abstract class AdmModel extends BaseModel
{
    use HasFactory;

    /**
     * 主キーはUUIDを採用するためstring
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 主キーはUUIDを採用するため自動incrementを無効化する
     * @var bool
     */
    public $incrementing = false;

    protected $connection = Database::ADMIN_CONNECTION;

    public function newUniqueId()
    {
        return Str::orderedUuid()->toString();
    }
}
