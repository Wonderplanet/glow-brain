using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattleRewardList.Domain.Model;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Translator
{
    public class AdventBattleRaidTotalScoreRewardCellViewModelTranslator
    {
        public static AdventBattleRaidTotalScoreRewardCellViewModel ToAdventBattleRaidTotalScoreRewardCellViewModel(
            AdventBattleRaidTotalScoreRewardModel model)
        {
            return new AdventBattleRaidTotalScoreRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RewardCondition,
                model.DidReceiveReward);
        }
    }
}