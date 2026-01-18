<?php

namespace App\Models\Usr;

use App\Constants\Database;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsrUnitSummary extends Model
{
    protected $connection = Database::TIDB_CONNECTION;
    protected $table = 'usr_unit_summaries';
    protected $primaryKey = 'usr_user_id';
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'usr_user_id',
        'grade_level_total_count',
    ];

    protected $casts = [
        'grade_level_total_count' => 'integer',
    ];

    public function usrUser(): BelongsTo
    {
        return $this->belongsTo(UsrUser::class, 'usr_user_id', 'id');
    }
}
