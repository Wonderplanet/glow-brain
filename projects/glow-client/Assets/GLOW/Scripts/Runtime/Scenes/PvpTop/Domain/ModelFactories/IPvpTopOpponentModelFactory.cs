using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.Model;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public interface IPvpTopOpponentModelFactory
    {
        IReadOnlyList<PvpTopOpponentModel> Create(
            IReadOnlyList<OpponentSelectStatusModel> opponentSelectStatusModels,
            MasterDataId mstPvpId);
    }
}
