<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Models;

use App\Domain\Resource\Mst\Entities\MstApiActionEntity as Entity;
use App\Domain\Resource\Traits\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MstApiAction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'mst';

    /**
     * @var array<string, string>
     * id : Doctrine\DBAL\Types\StringType
     * api_path : Doctrine\DBAL\Types\StringType
     * through_app : Doctrine\DBAL\Types\IntegerType
     * through_master : Doctrine\DBAL\Types\IntegerType
     * through_asset : Doctrine\DBAL\Types\IntegerType
     * through_date : Doctrine\DBAL\Types\IntegerType
     * release_key : Doctrine\DBAL\Types\BigIntType
     * resource : Doctrine\DBAL\Types\JsonType
     */
    protected $casts = [
        'id' => 'string',
        'api_path' => 'string',
        'through_app' => 'integer',
        'through_master' => 'integer',
        'through_asset' => 'integer',
        'through_date' => 'integer',
        'release_key' => 'integer',
        'resource' => 'json',
    ];

    /**
     * @return Entity
     */
    public function toEntity()
    {
        return new Entity(
            $this->id,
            $this->api_path,
            $this->through_master,
            $this->through_asset,
            $this->through_app,
            $this->through_date,
        );
    }
}
