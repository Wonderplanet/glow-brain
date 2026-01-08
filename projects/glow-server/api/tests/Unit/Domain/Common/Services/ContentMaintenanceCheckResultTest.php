<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Services;

use App\Domain\Common\Enums\ContentMaintenanceType;
use App\Domain\Common\Entities\ContentMaintenanceCheckResult;
use PHPUnit\Framework\TestCase;

class ContentMaintenanceCheckResultTest extends TestCase
{
    public function test_shouldBlockAccess_コンテンツタイプが見つからない場合はスルー()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: null,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertFalse($result->shouldBlockAccess());
    }

    public function test_shouldBlockAccess_コインクエスト以外はスルー()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::ENHANCE_QUEST,
            isEnhanceQuest: false, // コイン獲得クエストではない
            isUnderContentMaintenance: true, // メンテナンス中でも
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertFalse($result->shouldBlockAccess()); // 経験値クエストはスルー
    }

    public function test_shouldBlockAccess_コインクエストの場合はブロック()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::ENHANCE_QUEST,
            isEnhanceQuest: true, // コイン獲得クエスト
            isUnderContentMaintenance: true, // メンテナンス中
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertTrue($result->shouldBlockAccess()); // 経験値クエストはスルー
    }

    public function test_shouldBlockAccess_全体メンテナンス中はブロック()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::ADVENT_BATTLE,
            isEnhanceQuest: false,
            isUnderContentMaintenance: true,
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertTrue($result->shouldBlockAccess());
    }

    public function test_shouldBlockAccess_個別メンテナンス中はブロック()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::GACHA,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: true,
            contentId: 'gacha_001'
        );

        // Exercise & Verify
        $this->assertTrue($result->shouldBlockAccess());
    }

    public function test_shouldBlockAccess_メンテナンス中でない場合はスルー()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::PVP,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertFalse($result->shouldBlockAccess());
    }

    public function test_shouldBlockCleanupAccess_コンテンツタイプが見つからない場合はブロック()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: null,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertTrue($result->shouldBlockCleanupAccess());
    }

    public function test_shouldBlockCleanupAccess_コインクエストはスルー()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::ENHANCE_QUEST,
            isEnhanceQuest: true,
            isUnderContentMaintenance: true, // メンテナンス中でも
            isUnderContentMaintenanceByContentId: false,
            contentId: 'stage_001'
        );

        // Exercise & Verify
        $this->assertFalse($result->shouldBlockCleanupAccess()); // 経験値クエストはブロック
    }

    public function test_shouldBlockCleanupAccess_全体メンテナンス中はスルー()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::ADVENT_BATTLE,
            isEnhanceQuest: false,
            isUnderContentMaintenance: true,
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertFalse($result->shouldBlockCleanupAccess()); // メンテナンス中なのでクリーンアップ実行可能
    }

    public function test_shouldBlockCleanupAccess_個別メンテナンス中はスルー()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::GACHA,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: true,
            contentId: 'gacha_001'
        );

        // Exercise & Verify
        $this->assertFalse($result->shouldBlockCleanupAccess()); // 個別メンテナンス中なのでクリーンアップ実行可能
    }

    public function test_shouldBlockCleanupAccess_全体メンテナンス中でない場合はブロック()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::PVP,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: false,
            contentId: null
        );

        // Exercise & Verify
        $this->assertTrue($result->shouldBlockCleanupAccess()); // メンテナンス中でないのでクリーンアップ実行不可
    }

    public function test_shouldBlockCleanupAccess_個別IDありで対象外の場合はブロック()
    {
        // Setup
        $result = new ContentMaintenanceCheckResult(
            contentType: ContentMaintenanceType::GACHA,
            isEnhanceQuest: false,
            isUnderContentMaintenance: false,
            isUnderContentMaintenanceByContentId: false, // 個別メンテナンス対象外
            contentId: 'gacha_002' // IDはある
        );

        // Exercise & Verify
        $this->assertTrue($result->shouldBlockCleanupAccess()); // 個別メンテナンス対象外なのでブロック
    }
}
