using System.Linq;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.AdventBattle.Presentation.Calculator.Model;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;
using GLOW.Scenes.PassShop.Presentation.Translator;

namespace GLOW.Scenes.AdventBattle.Presentation.Translator
{
    public class AdventBattleTopViewModelTranslator
    {
        public static AdventBattleTopViewModel ToViewModel(
            AdventBattleTopUseCaseModel model,
            AdventBattleHighScoreGaugeModel highScoreGaugeModel,
            AdventBattleRankingCalculatingFlag calculatingRankings
            )
        {
            var highScoreRewards = model.HighScoreRewards
                .Select(AdventBattleHighScoreRewardViewModelTranslator.ToViewModel)
                .ToList();

            var highScoreGaugeViewModel = ToAdventBattleHighScoreGaugeViewModel(highScoreGaugeModel);
            
            // ここでのTotalScoreとMaxScoreはEmptyの場合は0として扱う(Emptyだと表示が---,---,---,--- ptになる)
            var totalScore = model.TotalScore.IsEmpty() ? AdventBattleScore.Zero : model.TotalScore;
            
            var maxScore = model.MaxScore.IsEmpty() ? AdventBattleScore.Zero : model.MaxScore;
            
            var raidTotalScore = model.RaidTotalScore.IsEmpty() ? AdventBattleRaidTotalScore.Zero : model.RaidTotalScore;
            
            return new AdventBattleTopViewModel(
                model.MstAdventBattleId,
                model.BattleType,
                model.EventBonusGroupId,
                model.ChallengeableCount,
                model.AdChallengeableCount,
                model.AdventBattleChallengeType,
                totalScore,
                model.RequiredLowerScore,
                maxScore,
                raidTotalScore,
                model.RequiredLowerNextRewardRaidTotalScore,
                model.CurrentRankType,
                model.CurrentScoreRankLevel,
                model.DisplayEnemyUnitFirst,
                model.DisplayEnemyUnitSecond,
                model.DisplayEnemyUnitThird,
                model.KomaBackgroundAssetPath,
                highScoreRewards,
                model.AdventBattleRemainingTimeSpan,
                model.PartyName,
                highScoreGaugeViewModel,
                model.ExistsSpecialRule,
                model.MissionBadge,
                calculatingRankings,
                model.AdventBattleName,
                model.AdventBattleBossDescription,
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(model.HeldAdSkipPassInfoModel),
                model.CampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList());
        }

        static AdventBattleHighScoreGaugeViewModel ToAdventBattleHighScoreGaugeViewModel(
            AdventBattleHighScoreGaugeModel model)
        {
            return new AdventBattleHighScoreGaugeViewModel(
                model.CurrentGaugeRate,
                model.RewardGaugeRateList);
        }
    }
}
