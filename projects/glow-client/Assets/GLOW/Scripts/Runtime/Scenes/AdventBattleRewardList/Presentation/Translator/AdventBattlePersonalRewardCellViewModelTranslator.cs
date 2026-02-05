using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.AdventBattleRewardList.Domain.Model;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Translator
{
    public class AdventBattlePersonalRewardCellViewModelTranslator
    {
        public static IAdventBattlePersonalCellViewModel ToAdventBattlePersonalRewardCellViewModel(
            IAdventBattlePersonalRewardModel model)
        {
            switch (model)
            {
                case AdventBattleSingleRankingRewardModel singleRankingRewardModel:
                    return ToAdventBattleSingleRankingRewardCellViewModel(singleRankingRewardModel);
                case AdventBattleIntervalRankingRewardModel intervalRankingRewardModel:
                    return ToAdventBattleIntervalRankingRewardCellViewModel(intervalRankingRewardModel);
                case AdventBattleScoreRankRewardModel scoreRankRewardModel:
                    return ToAdventBattleScoreRankRewardCellViewModel(scoreRankRewardModel);
                default:
                    return AdventBattleSingleRankingRewardCellViewModel.Empty;
            }
        }
        
        static AdventBattleSingleRankingRewardCellViewModel ToAdventBattleSingleRankingRewardCellViewModel(
            AdventBattleSingleRankingRewardModel model)
        {
            return new AdventBattleSingleRankingRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RankingRank);
        }
        
        static AdventBattleIntervalRankingRewardCellViewModel ToAdventBattleIntervalRankingRewardCellViewModel(
            AdventBattleIntervalRankingRewardModel model)
        {
            return new AdventBattleIntervalRankingRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RankingRankLower,
                model.RankingRankUpper);
        }
        
        static AdventBattleScoreRankRewardCellViewModel ToAdventBattleScoreRankRewardCellViewModel(
            AdventBattleScoreRankRewardModel model)
        {
            return new AdventBattleScoreRankRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RewardRankType,
                model.RewardRankLevel,
                model.RewardRankLowerScore,
                model.DidReceiveReward);
        }
        
    }
}