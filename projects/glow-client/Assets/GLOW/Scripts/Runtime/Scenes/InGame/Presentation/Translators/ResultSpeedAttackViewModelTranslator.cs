using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;

namespace GLOW.Scenes.InGame.Presentation.Translators
{
    public class ResultSpeedAttackViewModelTranslator
    {
        public static ResultSpeedAttackViewModel Translate(ResultSpeedAttackModel model)
        {
            return new ResultSpeedAttackViewModel(
                model.ClearTime,
                model.SpeedAttackRewards.Select(TranslateReward).ToList(),
                model.IsNewRecord);
        }

        static ResultSpeedAttackRewardViewModel TranslateReward(ResultSpeedAttackRewardModel model)
        {
            var icon = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.RewardIcon);
            return new ResultSpeedAttackRewardViewModel(icon, model.UpperClearTimeMs, model.IsAcquired, model.IsNew);
        }
    }
}
