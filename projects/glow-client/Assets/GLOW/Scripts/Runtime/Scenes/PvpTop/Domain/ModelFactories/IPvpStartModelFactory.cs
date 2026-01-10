using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;

namespace GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories
{
    public interface IPvpStartModelFactory
    {
        PvpStartUseCaseModel CreatePvpStartUseCaseModel(PvpStartResultModel pvpStartResultModel);
        PvpStartUseCaseModel CreatePvpStartUseCaseModel(PvpResumeResultModel pvpResumeResultModel);
    }
}
