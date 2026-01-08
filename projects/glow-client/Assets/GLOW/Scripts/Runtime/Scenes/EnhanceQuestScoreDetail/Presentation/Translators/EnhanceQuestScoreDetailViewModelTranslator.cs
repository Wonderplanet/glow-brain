using System.Linq;
using GLOW.Scenes.EnhanceQuestScoreDetail.Domain.Models;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.ViewModels;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Translators
{
    public static class EnhanceQuestScoreDetailViewModelTranslator
    {
        public static EnhanceQuestScoreDetailViewModel Translate(EnhanceQuestScoreDetailModel model)
        {
            var cells = model.Cells
                .Select(TranslateCellViewModel)
                .ToList();
            return new EnhanceQuestScoreDetailViewModel(cells);
        }

        static EnhanceQuestScoreDetailCellViewModel TranslateCellViewModel(EnhanceQuestScoreDetailCellModel model)
        {
            return new EnhanceQuestScoreDetailCellViewModel(
                model.EnhanceQuestMinThresholdScore,
                model.CoinRewardAmount,
                model.CoinRewardSizeType);
        }
    }
}
