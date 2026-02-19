using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainStageViewModel(
        MasterDataId MstStageId,
        StageNumber StageNumber,
        StageRecommendedLevel RecommendedLevel,
        StageIconAssetPath StageIconAssetPath,
        StageName StageName,
        StageConsumeStamina StageConsumeStamina,
        StagePlayableFlag PlayableFlag,
        StageIsSelected IsSelected,
        StageClearStatus StageClearStatus,
        ShowStageRequired ShowStageRequired,
        StageReleaseRequireSentence ReleaseRequireSentence,
        UnlimitedCalculableDateTimeOffset EndAt,
        StageClearCount DailyClearCount,
        ClearableCount DailyPlayableCount,
        SpeedAttackViewModel SpeedAttackViewModel,
        StageRewardCompleteFlag IsShowArtworkFragmentIcon,
        StageRewardCompleteFlag IsShowRewardCompleteIcon,
        ExistsSpecialRuleFlag ExistsSpecialRule,
        InGameSpecialRuleAchievedFlag IsSpecialRuleAchieved,
        IReadOnlyList<CampaignViewModel> CampaignViewModels,
        StaminaBoostBalloonType StaminaBoostBalloonType);
}
