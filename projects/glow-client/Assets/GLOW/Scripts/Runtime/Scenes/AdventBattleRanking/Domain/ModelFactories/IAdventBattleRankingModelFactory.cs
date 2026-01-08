using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattleRanking.Domain.Models;
namespace GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories
{
    public interface IAdventBattleRankingModelFactory
    {
        AdventBattleRankingElementUseCaseModel CreateAdventBattleRankingElementUseCaseModel(
            MasterDataId mstAdventBattleId,
            AdventBattleRankingResultModel adventBattleRankingResultModel,
            bool isEndOfEvent);
    }
}
