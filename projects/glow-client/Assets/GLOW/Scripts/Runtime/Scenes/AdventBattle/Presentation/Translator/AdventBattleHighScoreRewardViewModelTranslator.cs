using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattle.Presentation.Translator
{
    public class AdventBattleHighScoreRewardViewModelTranslator
    {
        public static AdventBattleHighScoreRewardViewModel ToViewModel(AdventBattleHighScoreRewardModel model)
        {
            return new AdventBattleHighScoreRewardViewModel(
                model.AdventBattleHighScore,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.Reward),
                model.RewardObtainedFlag,
                model.RewardPickupFlag);
        }
    }
}