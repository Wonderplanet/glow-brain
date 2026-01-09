using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;

namespace GLOW.Scenes.BattleResult.Presentation.Translators
{
    public class PvpResultViewModelTranslator
    {
        public static PvpResultViewModel ToViewModel(
            PvpResultModel pvpResultModel)
        {
            if (pvpResultModel.IsEmpty())
            {
                return PvpResultViewModel.Empty;
            }

            return new PvpResultViewModel(
                pvpResultModel.ResultType,
                pvpResultModel.PlayerDistanceRatio,
                pvpResultModel.OpponentDistanceRatio,
                pvpResultModel.FinishType);
        }
    }
}