<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Repositories;

use App\Domain\Resource\Usr\Models\UsrModel;
use Illuminate\Support\Collection;

/**
 * UsrModelMultiCacheRepositoryで扱うモデルクラスを
 * EloquentModelを使用しない App\Domain\Resource\Usr\Models\UsrModel
 * に調整したクラス
 */
class UsrModelMultiCacheRawRepository extends UsrModelMultiCacheRepository
{
    protected function dbSelectAll(string $usrUserId): Collection
    {
        /**
         * @var class-string<UsrModel> $modelClass
         */
        $modelClass = $this->modelClass;

        return $modelClass::query()
            ->where('usr_user_id', $usrUserId)
            ->get()
            ->map(function ($record) use ($modelClass) {
                return $modelClass::createFromRecord($record);
            });
    }
}
