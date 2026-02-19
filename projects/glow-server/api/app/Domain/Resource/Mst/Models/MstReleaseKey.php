<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use Illuminate\Database\Eloquent\Model;

class MstReleaseKey extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'release_key';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['start_at', 'description'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $connection = 'mst';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'release_key' => 'integer',
        'start_at' => 'datetime',
        'description' => 'string',
    ];
}
