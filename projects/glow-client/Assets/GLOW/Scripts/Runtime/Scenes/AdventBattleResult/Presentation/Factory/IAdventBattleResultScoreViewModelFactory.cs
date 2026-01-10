using GLOW.Scenes.AdventBattleResult.Domain.Model;
using GLOW.Scenes.AdventBattleResult.Presentation.ViewModel;

namespace GLOW.Scenes.AdventBattleResult.Presentation.Factory
{
    public interface IAdventBattleResultScoreViewModelFactory
    {
        AdventBattleResultScoreViewModel CreateAdventBattleResultScoreViewModel(
            AdventBattleResultScoreModel model);
    }
}