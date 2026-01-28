using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public class PvpUserRankStatusFactory : IPvpUserRankStatusFactory
    {
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }

        PvpUserRankStatus IPvpUserRankStatusFactory.Create(
            PvpPoint score)
        {
            var currentPvpRankModel = MstPvpDataRepository.GetCurrentPvpRankModel(score);
            var tier = new PvpTier(currentPvpRankModel.RankLevel.Value);
            return new PvpUserRankStatus(currentPvpRankModel.RankClassType, tier);
        }

    }
}
