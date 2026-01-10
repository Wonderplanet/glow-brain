using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Data.Translators
{
    public static class MstMissionDataTranslator
    {
        public static MstMissionDailyModel ToMstMissionDailyModel(MstMissionDailyData mstMissionDailyData,
            MstMissionDailyI18nData mstMissionDailyI18NData)
        {
            return new MstMissionDailyModel(
                new MasterDataId(mstMissionDailyData.Id),
                mstMissionDailyData.CriterionType,
                new CriterionValue(mstMissionDailyData.CriterionValue),
                new CriterionCount(mstMissionDailyData.CriterionCount),
                new MissionDescription(mstMissionDailyI18NData.Description),
                new GroupKey(mstMissionDailyData.GroupKey),
                new BonusPoint(mstMissionDailyData.BonusPoint),
                new MasterDataId(mstMissionDailyData.MstMissionRewardGroupId),
                new SortOrder(mstMissionDailyData.SortOrder),
                new DestinationScene(mstMissionDailyData.DestinationScene));
        }

        public static MstMissionWeeklyModel ToMstMissionWeeklyModel(MstMissionWeeklyData mstMissionWeeklyData,
            MstMissionWeeklyI18nData mstMissionWeeklyI18NData)
        {
            return new MstMissionWeeklyModel(
                new MasterDataId(mstMissionWeeklyData.Id),
                mstMissionWeeklyData.CriterionType,
                new CriterionValue(mstMissionWeeklyData.CriterionValue),
                new CriterionCount(mstMissionWeeklyData.CriterionCount),
                new MissionDescription(mstMissionWeeklyI18NData.Description),
                new GroupKey(mstMissionWeeklyData.GroupKey),
                new BonusPoint(mstMissionWeeklyData.BonusPoint),
                new MasterDataId(mstMissionWeeklyData.MstMissionRewardGroupId),
                new SortOrder(mstMissionWeeklyData.SortOrder),
                new DestinationScene(mstMissionWeeklyData.DestinationScene));
        }

        public static MstMissionDailyBonusModel ToMstMissionDailyBonusModel(MstMissionDailyBonusData mstMissionDailyBonusData)
        {
            return new MstMissionDailyBonusModel(
                new MasterDataId(mstMissionDailyBonusData.Id),
                mstMissionDailyBonusData.MissionDailyBonusType,
                new LoginDayCount(mstMissionDailyBonusData.LoginDayCount),
                new MasterDataId(mstMissionDailyBonusData.MstMissionRewardGroupId),
                new SortOrder(mstMissionDailyBonusData.SortOrder));
        }

        public static MstMissionAchievementModel ToMstMissionAchievementModel(MstMissionAchievementData mstMissionAchievementData,
            MstMissionAchievementI18nData mstMissionAchievementI18NData)
        {
            return new MstMissionAchievementModel(
                new MasterDataId(mstMissionAchievementData.Id),
                mstMissionAchievementData.CriterionType,
                new CriterionValue(mstMissionAchievementData.CriterionValue),
                new CriterionCount(mstMissionAchievementData.CriterionCount),
                new MissionDescription(mstMissionAchievementI18NData.Description),
                mstMissionAchievementData.UnlockCriterionType,
                new CriterionValue(mstMissionAchievementData.UnlockCriterionValue),
                mstMissionAchievementData.UnlockCriterionCount== null ? CriterionCount.Empty : new CriterionCount(mstMissionAchievementData.UnlockCriterionCount.Value),
                new GroupKey(mstMissionAchievementData.GroupKey),
                new MasterDataId(mstMissionAchievementData.MstMissionRewardGroupId),
                new SortOrder(mstMissionAchievementData.SortOrder),
                new DestinationScene(mstMissionAchievementData.DestinationScene),
                new MasterDataId("0"),
                new UnlockOrder(0));
        }

        public static MstMissionAchievementModel ToMstMissionAchievementModel(MstMissionAchievementData mstMissionAchievementData,
            MstMissionAchievementI18nData mstMissionAchievementI18NData, MstMissionAchievementDependencyData mstMissionAchievementDependencyData)
        {
            return new MstMissionAchievementModel(
                new MasterDataId(mstMissionAchievementData.Id),
                mstMissionAchievementData.CriterionType,
                new CriterionValue(mstMissionAchievementData.CriterionValue),
                new CriterionCount(mstMissionAchievementData.CriterionCount),
                new MissionDescription(mstMissionAchievementI18NData.Description),
                mstMissionAchievementData.UnlockCriterionType,
                new CriterionValue(mstMissionAchievementData.UnlockCriterionValue),
                mstMissionAchievementData.UnlockCriterionCount== null ? CriterionCount.Empty : new CriterionCount(mstMissionAchievementData.UnlockCriterionCount.Value),
                new GroupKey(mstMissionAchievementData.GroupKey),
                new MasterDataId(mstMissionAchievementData.MstMissionRewardGroupId),
                new SortOrder(mstMissionAchievementData.SortOrder),
                new DestinationScene(mstMissionAchievementData.DestinationScene),
                new MasterDataId(mstMissionAchievementDependencyData?.GroupId),
                new UnlockOrder(mstMissionAchievementDependencyData?.UnlockOrder ?? 0));
        }
        
        public static MstMissionBeginnerModel ToMstMissionBeginnerModel(MstMissionBeginnerData mstMissionBeginnerData,
            MstMissionBeginnerI18nData mstMissionBeginnerI18NData)
        {
            return new MstMissionBeginnerModel(
                new MasterDataId(mstMissionBeginnerData.Id),
                mstMissionBeginnerData.CriterionType,
                new CriterionValue(mstMissionBeginnerData.CriterionValue),
                new CriterionCount(mstMissionBeginnerData.CriterionCount),
                new BeginnerMissionDayNumber(mstMissionBeginnerData.UnlockDay),
                new MissionDescription(mstMissionBeginnerI18NData.Description),
                new GroupKey(mstMissionBeginnerData.GroupKey),
                new BonusPoint(mstMissionBeginnerData.BonusPoint),
                new MasterDataId(mstMissionBeginnerData.MstMissionRewardGroupId),
                new SortOrder(mstMissionBeginnerData.SortOrder),
                new DestinationScene(mstMissionBeginnerData.DestinationScene));
        }

        public static MstMissionAchievementDependencyModel ToMstMissionAchievementDependencyModel(
            MstMissionAchievementDependencyData mstMissionAchievementDependencyData)
        {
            return new MstMissionAchievementDependencyModel(
                new MasterDataId(mstMissionAchievementDependencyData.Id),
                new MasterDataId(mstMissionAchievementDependencyData.GroupId),
                new MasterDataId(mstMissionAchievementDependencyData.MstMissionAchievementId),
                new UnlockOrder(mstMissionAchievementDependencyData.UnlockOrder));
        }
        
        public static MstMissionEventModel ToMstMissionEventModel(MstMissionEventData mstMissionEventData,
            MstMissionEventI18nData mstMissionEventI18nData, MstMissionEventDependencyData mstMissionEventDependencyData)
        {
            return new MstMissionEventModel(
                new MasterDataId(mstMissionEventData.Id),
                new MasterDataId(mstMissionEventData.MstEventId),
                mstMissionEventData.CriterionType,
                new CriterionValue(mstMissionEventData.CriterionValue),
                new CriterionCount(mstMissionEventData.CriterionCount),
                new MissionDescription(mstMissionEventI18nData.Description),
                mstMissionEventData.UnlockCriterionType,
                new CriterionValue(mstMissionEventData.UnlockCriterionValue),
                mstMissionEventData.UnlockCriterionCount== null ? CriterionCount.Empty : new CriterionCount(mstMissionEventData.UnlockCriterionCount.Value),
                new GroupKey(mstMissionEventData.GroupKey),
                new MasterDataId(mstMissionEventData.MstMissionRewardGroupId),
                new SortOrder(mstMissionEventData.SortOrder),
                new DestinationScene(mstMissionEventData.DestinationScene),
                mstMissionEventData.EventCategory ?? EventCategory.None,
                new MasterDataId(mstMissionEventDependencyData?.GroupId),
                new UnlockOrder(mstMissionEventDependencyData?.UnlockOrder ?? 0));
        }
        
        public static MstMissionLimitedTermModel ToMstMissionLimitedTermModel(MstMissionLimitedTermData mstMissionLimitedTermData,
            MstMissionLimitedTermI18nData mstMissionLimitedTermI18nData, MstMissionLimitedTermDependencyData mstMissionLimitedTermDependencyData)
        {
            var dependencyGroupId = mstMissionLimitedTermDependencyData == null
                ? MasterDataId.Empty
                : new MasterDataId(mstMissionLimitedTermDependencyData.GroupId);
            
            var dependencyUnlockOrder = mstMissionLimitedTermDependencyData == null
                ? new UnlockOrder(0)
                : new UnlockOrder(mstMissionLimitedTermDependencyData.UnlockOrder);
            
            return new MstMissionLimitedTermModel(
                new MasterDataId(mstMissionLimitedTermData.Id),
                new MissionProgressGroupKey(mstMissionLimitedTermData.MstMissionRewardGroupId),
                mstMissionLimitedTermData.CriterionType,
                new CriterionValue(mstMissionLimitedTermData.CriterionValue),
                new CriterionCount(mstMissionLimitedTermData.CriterionCount),
                new MissionDescription(mstMissionLimitedTermI18nData.Description),
                mstMissionLimitedTermData.MissionCategory,
                new MasterDataId(mstMissionLimitedTermData.MstMissionRewardGroupId),
                new SortOrder(mstMissionLimitedTermData.SortOrder),
                new DestinationScene(mstMissionLimitedTermData.DestinationScene),
                dependencyGroupId,
                dependencyUnlockOrder,
                new MissionStartDate(mstMissionLimitedTermData.StartAt),
                new MissionEndDate(mstMissionLimitedTermData.EndAt));
        }
        
        public static MstMissionEventDependencyModel ToMstMissionEventDependencyModel(
            MstMissionEventDependencyData mstMissionEventDependencyData)
        {
            return new MstMissionEventDependencyModel(
                new MasterDataId(mstMissionEventDependencyData.Id),
                new MasterDataId(mstMissionEventDependencyData.GroupId),
                new MasterDataId(mstMissionEventDependencyData.MstMissionEventId),
                new UnlockOrder(mstMissionEventDependencyData.UnlockOrder));
        }
        
        public static MstMissionLimitedTermDependencyModel ToMstMissionLimitedTermDependencyModel(
            MstMissionLimitedTermDependencyData mstMissionLimitedTermDependencyData)
        {
            return new MstMissionLimitedTermDependencyModel(
                new MasterDataId(mstMissionLimitedTermDependencyData.Id),
                new MasterDataId(mstMissionLimitedTermDependencyData.GroupId),
                new MasterDataId(mstMissionLimitedTermDependencyData.MstMissionLimitedTermId),
                new UnlockOrder(mstMissionLimitedTermDependencyData.UnlockOrder));
        }
        
        public static MstMissionBeginnerPromptPhraseModel ToMstMissionBeginnerPromptPhraseModel(
            MstMissionBeginnerPromptPhraseI18nData mstMissionBeginnerPromptPhraseData)
        {
            return new MstMissionBeginnerPromptPhraseModel(
                new MasterDataId(mstMissionBeginnerPromptPhraseData.Id),
                new BeginnerMissionPromptPhrase(mstMissionBeginnerPromptPhraseData.PromptPhraseText),
                mstMissionBeginnerPromptPhraseData.StartAt,
                mstMissionBeginnerPromptPhraseData.EndAt);
        }
    }
}
