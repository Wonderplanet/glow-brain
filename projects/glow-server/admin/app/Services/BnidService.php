<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Auth\Services\AccessTokenService;
use App\Models\Usr\UsrDevice;

/**
 * Bnidに関するサービス
 */
class BnidService
{
    public function __construct(
        private AccessTokenService $accessTokenService,
    ) {
    }

    /**
     * BNID連携解除
     * @param string $usrUserId
     * @return bool
     */
    public function unlinkBnid(
        string $usrUserId
    ): bool {
        $usrDevices = UsrDevice::query()
            ->where('usr_user_id', $usrUserId)
            ->whereNotNull('bnid_linked_at')
            ->get();
        if ($usrDevices->isEmpty()) {
            return false;
        }

        foreach ($usrDevices as $usrDevice) {
            $usrDevice->delete();
        }

        $this->accessTokenService->delete($usrUserId);
        return true;
    }

}
