<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

use App\Domain\Common\Enums\ContentMaintenanceType;

/**
 * メンテナンス判定結果を格納するデータクラス
 */
readonly class ContentMaintenanceCheckResult
{
    public function __construct(
        public ?ContentMaintenanceType $contentType,
        public bool $isEnhanceQuest,
        public bool $isUnderContentMaintenance,
        public bool $isUnderContentMaintenanceByContentId,
        public ?string $contentId,
    ) {
    }

    /**
     * ContentMaintenanceCheck用の判定
     * メンテナンス中の場合にアクセスを拒否する
     * true: アクセス拒否（メンテナンス中）
     * false: アクセス許可（メンテナンス対象外 or メンテナンス中ではない）
     */
    public function shouldBlockAccess(): bool
    {
        // コンテンツタイプが取得できない場合はスルー
        if ($this->contentType === null) {
            return false;
        }

        // 部分メンテナンス中
        if ($this->isUnderContentMaintenance) {
            // stageAPIで設定されるtypeの場合、コインクエスト以外はスルー
            if ($this->contentType->isEnhanceQuestType()) {
                return $this->isEnhanceQuest;
            }
            return true;
        }

        // ID指定の部分メンテナンス中
        if ($this->isUnderContentMaintenanceByContentId) {
            return true;
        }

        return false;
    }

    /**
     * MaintenanceOnlyAccess用の判定
     * メンテナンス中でない場合にアクセスを拒否する
     * true: アクセス拒否（メンテナンス対象外 or メンテナンス中ではない）
     * false: アクセス許可（メンテナンス中）
     */
    public function shouldBlockCleanupAccess(): bool
    {
        return !$this->shouldBlockAccess();
    }
}
