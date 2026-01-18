using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;

namespace GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories
{
    public class PvpStartModelFactory : IPvpStartModelFactory
    {
        public PvpStartUseCaseModel CreatePvpStartUseCaseModel(
            PvpStartResultModel pvpStartResultModel)
        {
            return new PvpStartUseCaseModel(pvpStartResultModel.OpponentPvpStatus);
        }

        PvpStartUseCaseModel IPvpStartModelFactory.CreatePvpStartUseCaseModel(PvpResumeResultModel pvpResumeResultModel)
        {
            return new PvpStartUseCaseModel(pvpResumeResultModel.OpponentPvpStatus);
        }
    }
}
