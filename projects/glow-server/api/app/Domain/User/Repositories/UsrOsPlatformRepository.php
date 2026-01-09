<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\User\Models\UsrOsPlatform;
use App\Domain\User\Models\UsrOsPlatformInterface;
use Illuminate\Support\Collection;

class UsrOsPlatformRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrOsPlatform::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrOsPlatformInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'os_platform' => $model->getOsPlatform(),
            ];
        })->toArray();

        UsrOsPlatform::upsert(
            $upsertValues,
            ['usr_user_id', 'os_platform'],
            ['os_platform'],
        );
    }

    public function create(string $usrUserId, string $osPlatform): UsrOsPlatformInterface
    {
        $usrOsPlatform = new UsrOsPlatform();
        $usrOsPlatform->usr_user_id = $usrUserId;
        $usrOsPlatform->os_platform = $osPlatform;
        $this->syncModel($usrOsPlatform);

        return $usrOsPlatform;
    }

    public function getByUsrUserIdAndOsPlatform(string $usrUserId, string $osPlatform): ?UsrOsPlatformInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'os_platform',
            $osPlatform,
            function () use ($usrUserId, $osPlatform) {
                return UsrOsPlatform::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('os_platform', $osPlatform)
                    ->first();
            },
        );
    }
}
