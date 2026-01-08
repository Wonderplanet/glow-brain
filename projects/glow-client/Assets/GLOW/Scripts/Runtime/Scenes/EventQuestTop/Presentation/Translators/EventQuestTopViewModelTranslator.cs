using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.EventQuestTop.Domain.Models;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Translator;

namespace GLOW.Scenes.EventQuestTop.Presentation.Translators
{
    public static class EventQuestTopViewModelTranslator
    {
        public static EventQuestTopViewModel Translate(EventQuestTopUseCaseModel model)
        {
            return new EventQuestTopViewModel(
                model.MstEventId,
                model.MstQuestGroupId,
                model.EventName,
                model.QuestName,
                model.QuestCategoryName,
                model.Units.Select(TranslateUnit).ToList(),
                model.RemainingTime,
                model.InitialSelectStageMstStageId,
                model.Stages.Select(TranslateStage).ToList(),
                model.ShowStageReleaseAnimation,
                model.GettableArtworkFragmentNum,
                model.AcquiredArtworkFragmentNum,
                model.CampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList(),
                model.NewReleaseQuestNames
            );
        }

        static EventQuestTopUnitViewModel TranslateUnit(EventQuestTopUnitUseCaseModel model)
        {
            return new EventQuestTopUnitViewModel(
                model.UnitImageAssetPath,
                model.SpeechBalloonTexts
            );
        }
        static EventQuestTopElementViewModel TranslateStage(EventQuestTopElementModel model)
        {

            return new EventQuestTopElementViewModel(
                model.MstStageId,
                model.StageNumber,
                model.RecommendedLevel,
                model.StageIconAssetPath,
                model.StageName,
                model.StageConsumeStamina,
                model.StageReleaseStatus,
                model.StageClearStatus,
                model.StageReleaseRequireSentence,
                model.EndAt,
                model.DailyClearCount,
                model.DailyPlayableCount,
                SpeedAttackViewModelTranslator.Translate(model.SpeedAttackUseCaseModel),
                model.IsShowArtworkFragmentIcon,
                model.IsShowRewardCompleteIcon,
                model.ExistsSpecialRule,
                model.IsSpecialRuleAchieved,
                model.EventTopBackGroundAssetPath,
                model.StaminaBoostBalloonType
            );
        }

    }
}
