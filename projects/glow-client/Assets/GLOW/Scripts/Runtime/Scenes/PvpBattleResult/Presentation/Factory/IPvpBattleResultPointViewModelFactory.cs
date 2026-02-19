using GLOW.Scenes.PvpBattleResult.Domain.Model;
using GLOW.Scenes.PvpBattleResult.Presentation.ViewModel;

namespace GLOW.Scenes.PvpBattleResult.Presentation.Factory
{
    public interface IPvpBattleResultPointViewModelFactory
    {
        PvpBattleResultPointViewModel CreatePvpResultPointViewModel(
            PvpBattleResultPointModel model);
    }
}