<?php

namespace Tests\Feature\Domain\Mission;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal;
use App\Domain\Mission\Services\MissionNormalClearOnCallService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\Traits\TestMissionTrait;
use Tests\TestCase;

class MissionNormalClearOnCallServiceTest extends TestCase
{
    use TestMissionTrait;

    private MissionNormalClearOnCallService $missionNormalClearOnCallService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->missionNormalClearOnCallService = app(MissionNormalClearOnCallService::class);
    }

    public static function params_test_clearOnCall_クリア可能なミッションのみクリアできる()
    {
        $cases = [];
        // ClearOnCall対象のミッションタイプのケース
        foreach ([MissionType::BEGINNER, MissionType::ACHIEVEMENT] as $missionType) {
            $cases = array_merge($cases, [
                "{$missionType->value} クリアできる review_completed" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-review_completed",
                    'errorCode' => null,
                ],
                "{$missionType->value} クリアできる follow_completed" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-follow_completed",
                    'errorCode' => null,
                ],
                "{$missionType->value} クリアできない account_completed" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-account_completed",
                    'errorCode' => ErrorCode::MISSION_CANNOT_CLEAR,
                ],
                "{$missionType->value} クリアできる access_web" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-access_web",
                    'errorCode' => null,
                ],
                "{$missionType->value} クリアできない coin_collect" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-coin_collect",
                    'errorCode' => ErrorCode::MISSION_CANNOT_CLEAR,
                ],
                "{$missionType->value} クリアできない 存在しないマスタ" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-invalid",
                    'errorCode' => ErrorCode::MST_NOT_FOUND,
                ],
            ]);
        }

        // ClearOnCall対象外のミッションタイプのケース
        foreach ([MissionType::DAILY, MissionType::WEEKLY] as $missionType) {
            $cases = array_merge($cases, [
                "{$missionType->value} クリアできない review_completed" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-review_completed",
                    'errorCode' => ErrorCode::INVALID_PARAMETER,
                ],
                "{$missionType->value} クリアできない coin_collect" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-coin_collect",
                    'errorCode' => ErrorCode::INVALID_PARAMETER,
                ],
                "{$missionType->value} クリアできない 存在しないマスタ" => [
                    'missionType' => $missionType,
                    'targetMstMissionId' => "{$missionType->value}-invalid",
                    'errorCode' => ErrorCode::INVALID_PARAMETER,
                ],
            ]);
        }

        return $cases;
    }

    #[DataProvider('params_test_clearOnCall_クリア可能なミッションのみクリアできる')]
    public function test_clearOnCall_各ミッションタイプでクリア可能なミッションのみクリアできる(
        MissionType $missionType,
        string $targetMstMissionId,
        ?int $errorCode,
    ) {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $now = $this->fixTime();

        // mst
        $factory = $this->getMstFactory($missionType);
        $mstIdPrefix = $missionType->value;
        $factory->createMany([
            [
                'id' => $mstIdPrefix.'-review_completed',
                'criterion_type' => MissionCriterionType::REVIEW_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
            [
                'id' => $mstIdPrefix.'-follow_completed',
                'criterion_type' => MissionCriterionType::FOLLOW_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
            [
                'id' => $mstIdPrefix.'-account_completed',
                'criterion_type' => MissionCriterionType::ACCOUNT_COMPLETED, 'criterion_value' => null, 'criterion_count' => 1,
            ],
            [
                'id' => $mstIdPrefix.'-coin_collect',
                'criterion_type' => MissionCriterionType::COIN_COLLECT, 'criterion_value' => null, 'criterion_count' => 10,
            ],
            [
                'id' => $mstIdPrefix.'-access_web',
                'criterion_type' => MissionCriterionType::ACCESS_WEB, 'criterion_value' => null, 'criterion_count' => 1,
            ],
        ]);

        // usr
        $this->prepareUpdateBeginnerMission($usrUserId);

        if ($errorCode !== null) {
            $this->expectException(GameException::class);
            $this->expectExceptionCode($errorCode);
        }

        // Exercise
        $this->missionNormalClearOnCallService->clearOnCall($usrUserId, $now, $missionType, $targetMstMissionId);
        $this->saveAll();

        // Verify
        $usrMissions = UsrMissionNormal::where('usr_user_id', $usrUserId)->get();
        $this->assertCount(1, $usrMissions);

        $actual = $usrMissions->first();
        $this->checkUsrMissionNormal($actual, MissionStatus::CLEAR, 1, $now, $now, null);
    }
}
