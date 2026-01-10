using System.Linq;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Translator
{
    public class HomeMainViewModelTranslator
    {
        public HomeMainQuestViewModel TranslateToHomeMainQuestViewModel(HomePlayableQuestUseCaseModel entity)
        {
            var showRequiredTextTarget = entity.Stages.Where(s => !s.Playable.Value).FirstOrDefault();
                return new HomeMainQuestViewModel(
                    entity.MstQuestId,
                    entity.QuestName,
                    entity.QuestImageAssetPath,
                    entity.QuestLimitTime,
                    entity.InitialSelectStageMstStageId,
                    entity.Stages.Select(e =>
                    {
                        var showStageRequired = showRequiredTextTarget== null
                            ? false
                            : e.MstStageId == showRequiredTextTarget.MstStageId;
                        return TranslateToHomeMainStageViewModel(e,showStageRequired);
                    }).ToList(),
                    entity.ShowStageReleaseAnimation,
                    entity.ShowQuestReleaseAnimation,
                    entity.CurrentDifficulty,
                    entity.IsInAppReviewDisplay,
                    entity.IsDisplayTryStageText
                    );
        }

        HomeMainStageViewModel TranslateToHomeMainStageViewModel(HomePlayableStageUseCaseModel entity, bool showStageRequired)
        {
            return new HomeMainStageViewModel(
                entity.MstStageId,
                entity.StageNumber,
                entity.RecommendedLevel,
                StageIconAssetPath.FromAssetKey(entity.StageStageAssetKey),
                entity.StageName,
                entity.StageConsumeStamina,
                entity.Playable,
                entity.IsSelected,
                entity.StageClearStatus,
                new ShowStageRequired(showStageRequired),
                StageReleaseRequireSentence.CreateReleaseRequiredSentence(entity.ReleaseRequiredStageNumber.Value),
                entity.EndAt,
                entity.DailyClearCount,
                entity.DailyPlayableCount,
                SpeedAttackViewModelTranslator.Translate(entity.SpeedAttack),
                entity.IsShowArtworkFragmentIcon,
                entity.IsShowRewardCompleteIcon,
                entity.ExistsSpecialRule,
                entity.IsAchievedInGameSpecialRule,
                entity.CampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList(),
                entity.StaminaBoostBalloonType);
        }

        public HomeMainBadgeViewModel TranslateToHomeMainBadgeViewModel(HomeMainBadgeModel entity)
        {
            return new HomeMainBadgeViewModel(
                entity.DailyMission,
                entity.EventMission,
                entity.BeginnerMission,
                entity.Encyclopedia,
                entity.IdleIncentive,
                entity.Announcement,
                entity.MessageBox);
        }

    }
}
