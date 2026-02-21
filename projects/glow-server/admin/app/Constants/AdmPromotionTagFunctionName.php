<?php

declare(strict_types=1);

namespace App\Constants;

use App\Filament\Resources\AdmGachaCautionResource;
use App\Filament\Resources\AdmInformationResource;
use App\Filament\Resources\AdmS3ObjectResource;
use App\Filament\Resources\IgnSettingResource;
use App\Filament\Resources\MessageAllResource;
use App\Filament\Resources\MngJumpPlusRewardScheduleResource;
use App\Traits\EnumTrait;

enum AdmPromotionTagFunctionName: string
{
    use EnumTrait;

    case INFORMATION = 'Information';
    case IGN = 'Ign';
    case JUMP_PLUS_REWARD = 'JumpPlusReward';
    case GACHA_CAUTION = 'GachaCaution';
    case S3_OBJECT = 'S3Object';
    case MESSAGE_DISTRIBUTION = 'MessageDistribution';

    public function label(): string
    {
        return match ($this) {
            self::INFORMATION => 'お知らせ',
            self::IGN => 'IGN',
            self::JUMP_PLUS_REWARD => 'ジャンプ+連携報酬',
            self::GACHA_CAUTION => 'ガシャ注意事項',
            self::S3_OBJECT => 'S3アセット管理',
            self::MESSAGE_DISTRIBUTION => 'メッセージ配布',
        };
    }

    public function getFunctionPageUrl(string $admPromotionTagId): string
    {
        return match ($this) {
            self::INFORMATION => AdmInformationResource::getUrl('index') . '?tableFilters[adm_promotion_tag_id][value]=' . urlencode($admPromotionTagId),
            self::IGN => IgnSettingResource::getUrl('index') . '?tableFilters[adm_promotion_tag_id][value]=' . urlencode($admPromotionTagId),
            self::JUMP_PLUS_REWARD => MngJumpPlusRewardScheduleResource::getUrl('index') . '?tableFilters[adm_promotion_tag_id][value]=' . urlencode($admPromotionTagId),
            self::GACHA_CAUTION => AdmGachaCautionResource::getUrl('index') . '?tableFilters[adm_promotion_tag_id][value]=' . urlencode($admPromotionTagId),
            self::S3_OBJECT => AdmS3ObjectResource::getUrl('index') . '?tableFilters[adm_promotion_tag_id][value]=' . urlencode($admPromotionTagId),
            self::MESSAGE_DISTRIBUTION => MessageAllResource::getUrl('index') . '?tableFilters[adm_promotion_tag_id][value]=' . urlencode($admPromotionTagId),
        };
    }
}
