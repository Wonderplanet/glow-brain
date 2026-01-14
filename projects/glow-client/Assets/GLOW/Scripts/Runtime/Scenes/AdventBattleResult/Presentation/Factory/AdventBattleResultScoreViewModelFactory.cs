using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.AdventBattleResult.Presentation.ValueObject;
using GLOW.Scenes.AdventBattleResult.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleResult.Presentation.Factory
{
    public class AdventBattleResultScoreViewModelFactory : IAdventBattleResultScoreViewModelFactory
    {
        AdventBattleResultScoreViewModel IAdventBattleResultScoreViewModelFactory.CreateAdventBattleResultScoreViewModel(
            AdventBattleResultScoreModel model)
        {
            var targetModels = model.AdventBattleResultScoreRankTargetModels;

            var iconViewModels = model.CommonReceiveResourceModels
                .Select(m => m.PlayerResourceModel)
                .ToList();

            return new AdventBattleResultScoreViewModel(
                model.CurrentRankType,
                model.CurrentScoreRankLevel,
                CreateAdventBattleResultScoreRankTargetViewModels(targetModels),
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(iconViewModels),
                model.DamageScore,
                model.EnemyDefeatScore,
                model.BossEnemyDefeatScore);
        }

        IReadOnlyList<AdventBattleResultScoreRankTargetViewModel> CreateAdventBattleResultScoreRankTargetViewModels(
            IReadOnlyList<AdventBattleResultScoreRankTargetModel> targetModels)
        {
            return targetModels.Select(model =>
                {
                    var beforeScore = model.BeforeTotalScore;
                    var targetScore = AdventBattleScore.Min(model.TargetRankLowerRequiredScore, model.AfterTotalScore);

                    var beforeGaugeRate = CalculateGaugeRate(
                        beforeScore,
                        model.BeforeLowerRequiredScore,
                        model.TargetRankLowerRequiredScore);
                    var afterGaugeRate = CalculateGaugeRate(
                        targetScore,
                        model.BeforeLowerRequiredScore,
                        model.TargetRankLowerRequiredScore);
                    return new AdventBattleResultScoreRankTargetViewModel(
                        model.BeforeTotalScore,
                        model.AfterTotalScore,
                        model.TargetRankLowerRequiredScore,
                        model.TargetRankType,
                        model.TargetScoreRankLevel,
                        beforeGaugeRate,
                        afterGaugeRate);
                })
                .ToList();
        }

        AdventBattleResultRankAnimationGaugeRate CalculateGaugeRate(
            AdventBattleScore currentTotalScore,
            AdventBattleScore beforeRequiredLowerScore,
            AdventBattleScore targetRequiredLowerScore)
        {
            if (targetRequiredLowerScore.IsEmpty())
            {
                return AdventBattleResultRankAnimationGaugeRate.One;
            }

            float currentTotalScoreValue = (currentTotalScore - beforeRequiredLowerScore).Value;
            float targetRequiredLowerScoreValue = (targetRequiredLowerScore - beforeRequiredLowerScore).Value;
            if(targetRequiredLowerScoreValue == 0)
            {
                // 0除算を防ぐため、0の場合は1を返す
                return AdventBattleResultRankAnimationGaugeRate.One;
            }

            var gaugeRate = new AdventBattleResultRankAnimationGaugeRate(currentTotalScoreValue / targetRequiredLowerScoreValue);
            return gaugeRate;
        }
    }
}
