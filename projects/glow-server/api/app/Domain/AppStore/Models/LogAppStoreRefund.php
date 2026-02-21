<?php

declare(strict_types=1);

namespace App\Domain\AppStore\Models;

use App\Domain\Constants\Database;
use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids as BaseHasUuids;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * @property string $id
 * @property string $transaction_id
 * @property int    $price
 * @property string $refunded_at
 * @property string $signed_payload
 */
class LogAppStoreRefund extends BaseModel
{
    use BaseHasUuids;
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

    protected $connection = Database::TIDB_CONNECTION;

    protected $guarded = [
    ];

    protected $casts = [
    ];

    /**
     * @param array<mixed> $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->id)) {
            $this->id = $this->newUniqueId();
        }
    }
}
