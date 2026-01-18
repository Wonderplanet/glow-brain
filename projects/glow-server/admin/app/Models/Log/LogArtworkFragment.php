<?php

namespace App\Models\Log;

use App\Constants\Database;
use App\Contracts\IAthenaModel;
use App\Domain\Encyclopedia\Models\LogArtworkFragment as BaseLogArtworkFragment;
use App\Traits\AthenaModelTrait;

class LogArtworkFragment extends BaseLogArtworkFragment implements IAthenaModel
{
    use AthenaModelTrait;

    protected $connection = Database::TIDB_CONNECTION;
}
