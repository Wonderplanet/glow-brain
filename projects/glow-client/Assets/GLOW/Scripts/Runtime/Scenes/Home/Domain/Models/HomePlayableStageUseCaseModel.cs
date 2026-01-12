using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomePlayableStageUseCaseModel(
        MasterDataId MstStageId,
        StageNumber StageNumber,
        StageName StageName,
        StageRecommendedLevel RecommendedLevel,
        StageAssetKey StageStageAssetKey,
        StageConsumeStamina StageConsumeStamina,
        StageNumber ReleaseRequiredStageNumber,
        StagePlayableFlag Playable,
        StageIsSelected IsSelected,
        StageClearStatus StageClearStatus,
        UnlimitedCalculableDateTimeOffset EndAt,
        StageClearCount DailyClearCount,
        ClearableCount DailyPlayableCount,
        SpeedAttackUseCaseModel SpeedAttack,
        StageRewardCompleteFlag IsShowArtworkFragmentIcon,
        StageRewardCompleteFlag IsShowRewardCompleteIcon,
        InGameSpecialRuleAchievedFlag IsAchievedInGameSpecialRule,
        ExistsSpecialRuleFlag ExistsSpecialRule,
        IReadOnlyList<CampaignModel> CampaignModels,
        StaminaBoostBalloonType StaminaBoostBalloonType)
    {
        public static HomePlayableStageUseCaseModel EmptyNonOpen(
            MstStageModel mst,
            StageNumber releaseRequiredStageNumber,
            UnlimitedCalculableDateTimeOffset questEndDate,
            IReadOnlyList<CampaignModel> campaignModels,
            StageConsumeStamina stageConsumeStamina)
        {
            return new HomePlayableStageUseCaseModel(
                mst.Id,
                mst.StageNumber,
                mst.Name,
                mst.RecommendedLevel,
                mst.StageAssetKey,
                stageConsumeStamina,
                releaseRequiredStageNumber,
                new StagePlayableFlag(false),
                new StageIsSelected(false),
                StageClearStatus.None,
                questEndDate,
                StageClearCount.Empty,
                ClearableCount.Empty,
                SpeedAttackUseCaseModel.Empty,
                StageRewardCompleteFlag.Empty,
                StageRewardCompleteFlag.Empty,
                InGameSpecialRuleAchievedFlag.True,
                ExistsSpecialRuleFlag.False,
                campaignModels,
                StaminaBoostBalloonType.None
            );
        }
        public static HomePlayableStageUseCaseModel EmptyOpened(
            MstStageModel mst,
            UnlimitedCalculableDateTimeOffset questEndDate,
            MstStageEventSettingModel eventSetting,
            SpeedAttackUseCaseModel speedAttack,
            InGameSpecialRuleAchievedFlag isAchievedInGameSpecialRule,
            ExistsSpecialRuleFlag existsSpecialRule,
            IReadOnlyList<CampaignModel> campaignModels,
            StageConsumeStamina stageConsumeStamina,
            StaminaBoostBalloonType staminaBoostBalloonType)
        {
            var stageClearCount = eventSetting.IsEmpty() ? StageClearCount.Empty : StageClearCount.Zero;
            var clearableCount = eventSetting.IsEmpty() ? ClearableCount.Empty : eventSetting.ClearableCount;
            return new HomePlayableStageUseCaseModel(
                mst.Id,
                mst.StageNumber,
                mst.Name,
                mst.RecommendedLevel,
                mst.StageAssetKey,
                stageConsumeStamina,
                StageNumber.Create(-1),
                new StagePlayableFlag(true),
                new StageIsSelected(false),
                StageClearStatus.New,
                questEndDate,
                stageClearCount,
                clearableCount,
                speedAttack,
                StageRewardCompleteFlag.Empty,
                StageRewardCompleteFlag.Empty,
                isAchievedInGameSpecialRule,
                existsSpecialRule,
                campaignModels,
                staminaBoostBalloonType
            );
        }
    };

}
