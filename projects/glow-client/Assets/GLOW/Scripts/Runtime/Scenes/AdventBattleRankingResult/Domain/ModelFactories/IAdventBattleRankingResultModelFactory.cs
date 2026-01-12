using GLOW.Core.Domain.Models.AdventBattle;
using AdventBattleRankingResultModel = GLOW.Scenes.AdventBattleRankingResult.Domain.Models.AdventBattleRankingResultModel;
namespace GLOW.Scenes.AdventBattleRankingResult.Domain.ModelFactories
{
    public interface IAdventBattleRankingResultModelFactory
    {
        AdventBattleRankingResultModel CreateAdventBattleRankingResultModel(AdventBattleInfoResultModel infoResultModel);
    }
}
