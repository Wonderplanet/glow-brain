using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.Home.Domain.Models;

namespace GLOW.Scenes.EventQuestTop.Domain.Models
{
    public record EventQuestTopElementModel(
        MasterDataId MstStageId,
        StageNumber StageNumber,
        StageRecommendedLevel RecommendedLevel,
        StageIconAssetPath StageIconAssetPath,
        StageName StageName,
        StageConsumeStamina StageConsumeStamina,
        StageNumber ReleaseRequiredStageNumber,
        StageReleaseStatus StageReleaseStatus,
        StageClearStatus StageClearStatus,
        UnlimitedCalculableDateTimeOffset EndAt,
        StageClearCount DailyClearCount,
        ClearableCount DailyPlayableCount,
        SpeedAttackUseCaseModel SpeedAttackUseCaseModel,
        StageRewardCompleteFlag IsShowArtworkFragmentIcon,
        StageRewardCompleteFlag IsShowRewardCompleteIcon,
        InGameSpecialRuleAchievedFlag IsSpecialRuleAchieved,
        ExistsSpecialRuleFlag ExistsSpecialRule,
        StageReleaseRequireSentence StageReleaseRequireSentence,
        KomaBackgroundAssetPath EventTopBackGroundAssetPath,
        StaminaBoostBalloonType StaminaBoostBalloonType
    )
    {
        public static EventQuestTopElementModel Empty { get; } = new EventQuestTopElementModel(
            MasterDataId.Empty,
            StageNumber.Empty,
            StageRecommendedLevel.Empty,
            StageIconAssetPath.Empty,
            StageName.Empty,
            StageConsumeStamina.Empty,
            StageNumber.Empty,
            StageReleaseStatus.Empty,
            StageClearStatus.None,
            new UnlimitedCalculableDateTimeOffset(UnlimitedCalculableDateTimeOffset.UnlimitedEndAt),
            StageClearCount.Empty,
            ClearableCount.Empty,
            SpeedAttackUseCaseModel.Empty,
            StageRewardCompleteFlag.False,
            StageRewardCompleteFlag.False,
            InGameSpecialRuleAchievedFlag.False,
            ExistsSpecialRuleFlag.False,
            StageReleaseRequireSentence.Empty,
            KomaBackgroundAssetPath.Empty,
            StaminaBoostBalloonType.None
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
