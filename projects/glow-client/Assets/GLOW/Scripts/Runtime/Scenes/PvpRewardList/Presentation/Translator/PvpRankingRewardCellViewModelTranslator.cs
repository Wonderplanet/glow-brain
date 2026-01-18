using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PvpRewardList.Domain.Model;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.PvpRewardList.Presentation.Translator
{
    public class PvpRankingRewardCellViewModelTranslator
    {
        public static IPvpRankingRewardCellViewModel ToPvpRankingRewardCellViewModel(
            IPvpRankingRewardModel model)
        {
            switch (model)
            {
                case PvpSingleRankingRewardModel singleRankingRewardModel:
                    return ToPvpSingleRankingRewardCellViewModel(singleRankingRewardModel);
                case PvpIntervalRankingRewardModel intervalRankingRewardModel:
                    return ToPvpIntervalRankingRewardCellViewModel(intervalRankingRewardModel);
                default:
                    return PvpSingleRankingRewardCellViewModel.Empty;
            }
        }
        
        static PvpSingleRankingRewardCellViewModel ToPvpSingleRankingRewardCellViewModel(
            PvpSingleRankingRewardModel model)
        {
            return new PvpSingleRankingRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RankingRank);
        }

        static PvpIntervalRankingRewardCellViewModel ToPvpIntervalRankingRewardCellViewModel(
            PvpIntervalRankingRewardModel model)
        {
            return new PvpIntervalRankingRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RankingRankLower,
                model.RankingRankUpper);
        }
    }
}