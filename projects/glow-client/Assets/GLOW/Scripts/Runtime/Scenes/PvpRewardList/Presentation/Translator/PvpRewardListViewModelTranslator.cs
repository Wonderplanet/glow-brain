using System.Linq;
using GLOW.Scenes.PvpRewardList.Domain.Model;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.PvpRewardList.Presentation.Translator
{
    public class PvpRewardListViewModelTranslator
    {
        public static PvpRewardListViewModel ToPvpRewardListViewModel(PvpRewardListModel model)
        {
            return new PvpRewardListViewModel(
                model.RemainingTimeSpan,
                model.RankingRewards.Select(
                    PvpRankingRewardCellViewModelTranslator.ToPvpRankingRewardCellViewModel)
                    .ToList(),
                model.PointRankRewards.Select(
                    PvpPointRankRewardCellViewModelTranslator.ToPvpPointRankRewardCellViewModel)
                    .ToList(),
                model.TotalScoreRewards.Select(
                    PvpTotalScoreRewardCellViewModelTranslator.ToPvpTotalScoreRewardCellViewModel)
                    .ToList()
            );
        }
    }
}