<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Models\UsrDevice;
use App\Domain\Auth\Models\UsrDeviceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UsrDeviceRepository
{
    /**
     * @throws \UnexpectedValueException
     */
    public function findByUuid(string $uuid): ?UsrDeviceInterface
    {
        return UsrDevice::where('uuid', $uuid)->first();
    }

    public function findById(string $id): UsrDeviceInterface
    {
        return UsrDevice::find($id);
    }

    public function findByUsrUserIdAndOsPlatform(string $usrUserId, string $osPlatform): Collection
    {
        return UsrDevice::query()
            ->where('usr_user_id', $usrUserId)
            ->where('os_platform', $osPlatform)
            ->get();
    }

    public function getByUsrUserId(string $usrUserId): Collection
    {
        return UsrDevice::query()
            ->where('usr_user_id', $usrUserId)
            ->get();
    }

    /** @api */
    public function deleteByUserId(string $usrUserId): void
    {
        UsrDevice::where('usr_user_id', $usrUserId)->delete();
    }

    /** @api */
    public function create(
        string $usrUserId,
        ?string $uuid = null,
        ?string $bnidLinkedAt = null,
        string $osPlatform = ''
    ): UsrDeviceInterface {
        $uuid ??= (string) Str::uuid();

        return UsrDevice::create([
            'usr_user_id' => $usrUserId,
            'uuid' => $uuid,
            'bnid_linked_at' => $bnidLinkedAt,
            'os_platform' => $osPlatform,
        ]);
    }

    /**
     * デバイスのBNID連携日時を更新する
     * @param string          $id
     * @param string          $usrUserId
     * @param CarbonImmutable $now
     * @return void
     */
    public function updateBnidLinkedAt(string $id, string $usrUserId, CarbonImmutable $now): void
    {
        UsrDevice::query()
            ->where('id', $id)
            ->where('usr_user_id', $usrUserId)
            ->update(['bnid_linked_at' => $now->toDateTimeString()]);
    }

    /**
     * デバイスを削除する
     * @param string $id
     * @param string $usrUserId
     * @return void
     */
    public function deleteByIdAndUsrUserId(string $id, string $usrUserId): void
    {
        UsrDevice::query()
            ->where('id', $id)
            ->where('usr_user_id', $usrUserId)
            ->delete();
    }
}
