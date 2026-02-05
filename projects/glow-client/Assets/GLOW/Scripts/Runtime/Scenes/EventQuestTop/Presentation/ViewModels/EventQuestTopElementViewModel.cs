using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.EventQuestTop.Presentation.ViewModels
{
    public record EventQuestTopElementViewModel(
        MasterDataId MstStageId,
        StageNumber StageNumber,
        StageRecommendedLevel RecommendedLevel,
        StageIconAssetPath StageIconAssetPath,
        StageName StageName,
        StageConsumeStamina StageConsumeStamina,
        StageReleaseStatus StageReleaseStatus,
        StageClearStatus StageClearStatus,
        StageReleaseRequireSentence ReleaseRequireSentence,
        UnlimitedCalculableDateTimeOffset EndAt,
        StageClearCount DailyClearCount,
        ClearableCount DailyPlayableCount,
        SpeedAttackViewModel SpeedAttackViewModel,
        StageRewardCompleteFlag IsShowArtworkFragmentIcon,
        StageRewardCompleteFlag IsShowRewardCompleteIcon,
        ExistsSpecialRuleFlag ExistsSpecialRule,
        InGameSpecialRuleAchievedFlag IsSpecialRuleAchieved,
        KomaBackgroundAssetPath EventTopBackGroundAssetPath,
        StaminaBoostBalloonType StaminaBoostBalloonType)
    {
        public static EventQuestTopElementViewModel Empty { get; } = new EventQuestTopElementViewModel(
            MasterDataId.Empty,
            StageNumber.Empty,
            StageRecommendedLevel.Empty,
            StageIconAssetPath.Empty,
            StageName.Empty,
            StageConsumeStamina.Empty,
            StageReleaseStatus.Empty,
            StageClearStatus.None,
            StageReleaseRequireSentence.Empty,
            new UnlimitedCalculableDateTimeOffset(UnlimitedCalculableDateTimeOffset.UnlimitedEndAt),
            StageClearCount.Empty,
            ClearableCount.Empty,
            SpeedAttackViewModel.Empty,
            StageRewardCompleteFlag.Empty,
            StageRewardCompleteFlag.Empty,
            ExistsSpecialRuleFlag.False,
            InGameSpecialRuleAchievedFlag.False,
            KomaBackgroundAssetPath.Empty,
            StaminaBoostBalloonType.None
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
