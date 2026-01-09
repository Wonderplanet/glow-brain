using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpRanking.Domain.Models;
namespace GLOW.Scenes.PvpRanking.Domain.ModelFactories
{
    public interface IPvpRankingModelFactory
    {
        PvpRankingElementUseCaseModel CreatePvpRankingElementUseCaseModel(
            PvpRankingResultModel pvpRankingResultModel,
            bool isPrevRanking);
    }
}
