using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PvpRewardList.Domain.Model;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;

namespace GLOW.Scenes.PvpRewardList.Presentation.Translator
{
    public static class PvpTotalScoreRewardCellViewModelTranslator
    {
        public static PvpTotalScoreRewardCellViewModel ToPvpTotalScoreRewardCellViewModel(PvpTotalScoreRewardModel model)
        {
            return new PvpTotalScoreRewardCellViewModel(
                model.Id,
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.Rewards),
                model.RequiredPoint,
                model.IsReceived
            );
        }
    }
}