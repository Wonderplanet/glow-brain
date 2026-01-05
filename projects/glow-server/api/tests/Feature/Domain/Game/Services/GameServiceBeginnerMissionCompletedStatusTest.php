<?php

declare(strict_types=1);

namespace Feature\Domain\Game\Services;

use App\Domain\Common\Enums\Language;
use App\Domain\Game\Services\GameService;
use App\Domain\Mission\Enums\MissionBeginnerStatus;
use App\Domain\Mission\Models\UsrMissionStatus;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Models\UsrUserLogin;
use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Models\UsrUserProfile;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * 初心者ミッション完了フラグのGameService::update時の挙動テスト
 *
 * 完了フラグ(usr_mission_status.beginner_mission_status)がCOMPLETED状態の時、
 * 複数回のログインで完了フラグのデータが変わらないことを確認する。
 */
class GameServiceBeginnerMissionCompletedStatusTest extends TestCase
{
    private GameService $gameService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameService = app(GameService::class);
    }

    /**
     * 初心者ミッション完了後のログイン時の完了フラグ検証用データプロバイダ
     *
     * @return array<string, array{hoursSinceLastLogin: int, daysSinceLastLogin: int}>
     */
    public static function params_test_初心者ミッション完了後のログイン時に完了フラグが変わらないことを確認(): array
    {
        return [
            '初心者ミッション完了直後のログイン時' => [
                'hoursSinceLastLogin' => 0,
                'daysSinceLastLogin' => 0,
            ],
            '同日2回目以降ログイン時' => [
                'hoursSinceLastLogin' => 2,
                'daysSinceLastLogin' => 0,
            ],
            '翌日初ログイン時' => [
                'hoursSinceLastLogin' => 0,
                'daysSinceLastLogin' => 1,
            ],
        ];
    }

    /**
     * 初心者ミッション完了後のログイン時に完了フラグが変わらないことを確認
     *
     * @param int $hoursSinceLastLogin
     * @param int $daysSinceLastLogin
     * @return void
     */
    #[DataProvider('params_test_初心者ミッション完了後のログイン時に完了フラグが変わらないことを確認')]
    public function test_初心者ミッション完了後のログイン時に完了フラグが変わらないことを確認(
        int $hoursSinceLastLogin,
        int $daysSinceLastLogin,
    ): void {
        // Setup: 初期ログイン時刻
        $now = $this->fixTime();

        // ユーザー作成
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $platform = 1; // iOS
        $language = Language::Ja->value;
        $gameStartAt = CarbonImmutable::parse($usrUser->getGameStartAt());

        // 必要なユーザーデータを作成
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 200,
        ]);

        $this->createDiamond($usrUserId, 100, 200, 300);

        UsrUserProfile::factory()->create([
            'usr_user_id' => $usrUserId,
        ]);

        UsrUserLogin::factory()->create([
            'usr_user_id' => $usrUserId,
            'login_count' => 10,
            'login_day_count' => 5,
            'login_continue_day_count' => 5,
            'last_login_at' => $now
                ->subDays($daysSinceLastLogin)
                ->subHours($hoursSinceLastLogin)
                ->toDateTimeString(),
        ]);

        // 完了状態の初心者ミッションステータスを作成
        UsrMissionStatus::factory()->create([
            'usr_user_id' => $usrUserId,
            'beginner_mission_status' => MissionBeginnerStatus::COMPLETED->value,
            'mission_unlocked_at' => $now->subDays(10)->toDateTimeString(), // 10日前に解放
            'latest_mst_hash' => 'test_hash_completed',
        ]);

        // Exercise: GameService::updateを実行
        $this->gameService->update(
            $usrUserId,
            $platform,
            $now,
            $language,
            $gameStartAt
        );

        // 変更を保存
        $this->saveAll();

        // Verify: 完了フラグが変わっていないことを確認
        $usrMissionStatus = UsrMissionStatus::where('usr_user_id', $usrUserId)->first();
        $this->assertNotNull($usrMissionStatus);
        $this->assertEquals(
            MissionBeginnerStatus::COMPLETED->value,
            $usrMissionStatus->beginner_mission_status,
        );
    }
}
