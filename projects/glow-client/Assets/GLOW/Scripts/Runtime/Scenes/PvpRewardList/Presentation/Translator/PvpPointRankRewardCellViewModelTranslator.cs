using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PvpRewardList.Domain.Model;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.PvpRewardList.Presentation.Translator
{
    public class PvpPointRankRewardCellViewModelTranslator
    {
        public static PvpRankRewardCellViewModel ToPvpPointRankRewardCellViewModel(PvpPointRankRewardModel model)
        {
            return new PvpRankRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RankType,
                model.RankLevel,
                model.RequiredPoint);
        }
    }
}