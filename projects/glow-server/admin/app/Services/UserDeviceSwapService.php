<?php

namespace App\Services;

use App\Models\Usr\UsrDevice;
use App\Models\Usr\UsrUser;
use App\Models\Usr\UsrUserProfile;
use App\Traits\DatabaseTransactionTrait;
use Exception;
use Illuminate\Support\Collection;

class UserDeviceSwapService
{
    use DatabaseTransactionTrait;

    /**
     * デバイス情報のusr_user_idを交換
     *
     * @param string $myId1
     * @param string $myId2
     * @return bool true: 成功, false: 失敗（ユーザーが見つからない、デバイスが見つからない、同一ユーザー等）
     */
    public function swapUserDevices(string $myId1, string $myId2): bool
    {
        return $this->transaction(function () use ($myId1, $myId2) {
            $usrUser1 = $this->getUsrUserByMyId($myId1);
            $usrUser2 = $this->getUsrUserByMyId($myId2);

            if (!$usrUser1 || !$usrUser2) {
                throw new Exception('指定されたユーザーが見つかりません');
            }

            if ($usrUser1->id === $usrUser2->id) {
                throw new Exception('同一ユーザーは指定できません');
            }

            $usrDevice1 = $this->getUsrDeviceByUsrUserId($usrUser1->id);
            $usrDevice2 = $this->getUsrDeviceByUsrUserId($usrUser2->id);

            if (!$usrDevice1 || !$usrDevice2) {
                throw new Exception('デバイス情報が見つかりません');
            }

            // デバイス情報のusr_user_idを交換
            $originalUsrUserId1 = $usrDevice1->usr_user_id;
            $originalUsrUserId2 = $usrDevice2->usr_user_id;

            $usrDevice1->usr_user_id = $originalUsrUserId2;
            $usrDevice1->save();

            $usrDevice2->usr_user_id = $originalUsrUserId1;
            $usrDevice2->save();

            return true;
        });
    }

    /**
     * マイIDでユーザーを検索
     */
    private function getUsrUserByMyId(string $myId): ?UsrUser
    {
        // my_idで検索
        $usrUserProfile = UsrUserProfile::where('my_id', $myId)->first();

        if ($usrUserProfile) {
            return UsrUser::query()->where('id', $usrUserProfile->usr_user_id)->first();
        }

        return null;
    }

    /**
     * ユーザーのデバイス情報を取得
     */
    private function getUsrDeviceByUsrUserId(string $usrUserId): ?UsrDevice
    {
        return UsrDevice::where('usr_user_id', $usrUserId)->first();
    }

    /**
     * ユーザー検索（my_idのみ）
     *
     * @param string $myId
     * @return Collection<UsrUser>
     */
    public function searchUsers(string $myId): Collection
    {
        $usrUsers = collect();

        // my_idで検索
        $usrUserProfile = UsrUserProfile::where('my_id', $myId)->first();
        if ($usrUserProfile) {
            $usrUser = UsrUser::query()->where('id', $usrUserProfile->usr_user_id)->first();
            if ($usrUser && $usrUser->profile) {
                $usrUsers->push($usrUser);
            }
        }

        return $usrUsers;
    }
}
