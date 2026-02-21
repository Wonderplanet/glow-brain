<?php

namespace Database\Seeders\Dummies;

use App\Domain\Auth\Services\AccessTokenService;
use App\Models\GenericUsrModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    /**
     * ダミーデータ生成
     */
    public function run(): void
    {
        $now = CarbonImmutable::now();
        $usrUserId = 'test_user_1';
        $usrDeviceId = 'test_user_device_1';
        $bnUserId = 'test_bn_user_1';

        $usrUserModel = (new GenericUsrModel())->setTableName('usr_users');
        $usrDeviceModel = (new GenericUsrModel())->setTableName('usr_devices');
        $usrUserProfileModel = (new GenericUsrModel())->setTableName('usr_user_profiles');
        $usrUserParameterModel = (new GenericUsrModel())->setTableName('usr_user_parameters');

        $usrUserModel->newQuery()->upsert(
            [
                [
                    'id' => $usrUserId,
                    'status' => 0,
                    'tutorial_status' => '',
                    'tos_version' => 0,
                    'privacy_policy_version' => 0,
                    'global_consent_version' => 0,
                    'bn_user_id' => $bnUserId,
                    'is_account_linking_restricted' => 0,
                    'client_uuid' => 'test_client_uuid_1',
                    'suspend_end_at' => null,
                    'game_start_at' => $now->toDateTimeString(),
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
            ['status', 'tutorial_status', 'tos_version', 'privacy_policy_version', 'global_consent_version', 'bn_user_id', 'is_account_linking_restricted', 'client_uuid', 'suspend_end_at', 'game_start_at', 'created_at', 'updated_at']
        );
        $usrDeviceModel->newQuery()->upsert(
            [
                [
                    'id' => $usrDeviceId,
                    'usr_user_id' => $usrUserId,
                    'uuid' => '',
                    'bnid_linked_at' => $now->subDay()->toDateTimeString(),
                    'os_platform' => 'android',
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
            ['usr_user_id', 'uuid', 'bnid_linked_at', 'created_at', 'updated_at']
        );
        $usrUserProfileModel->newQuery()->upsert(
            [
                [
                    'id' => 'test_user_profile_1',
                    'usr_user_id' => $usrUserId,
                    'my_id' => 'test_my_id_1',
                    'name' => 'test',
                    'is_change_name' => 1,
                    'birth_date' => '',
                    'mst_unit_id' => '',
                    'mst_emblem_id' => '',
                    'name_update_at' => null,
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
            ['usr_user_id', 'my_id', 'name', 'is_change_name', 'birth_date', 'mst_unit_id', 'mst_emblem_id', 'name_update_at', 'created_at', 'updated_at']
        );
        $usrUserParameterModel->newQuery()->upsert(
            [
                [
                    'id' => 'test_user_parameter_1',
                    'usr_user_id' => $usrUserId,
                    'level' => 1,
                    'exp' => 1,
                    'coin' => 1,
                    'stamina' => 100,
                    'stamina_updated_at' => $now->toDateTimeString(),
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                ],
            ],
            ['id'],
            ['usr_user_id', 'level', 'exp', 'coin', 'stamina', 'stamina_updated_at', 'created_at', 'updated_at']
        );

        $accessTokenService = app(AccessTokenService::class);
        $accessToken = $accessTokenService->create($usrUserId, $usrDeviceId, $now);
    }
}
