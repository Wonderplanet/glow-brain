using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Core.Domain.Repositories
{
    public interface IPvpTopCacheRepository
    {
        DateTimeOffset GetOpponentRefreshedTime();
        void SetOpponentRefreshedTime(DateTimeOffset refreshTime);

        PvpTopApiCallAllowedStatus GetPvpTopApiCallAllowedStatus();
        void SetPvpTopApiCallAllowedStatus(PvpTopApiCallAllowedStatus isPvpTopApiCallAllowed);

        PvpTopResultModel GetCachedPvpTopResultModel();
        void SetCachedPvpTopResultModel(PvpTopResultModel pvpTopResultModel);
        void SetCachedPvpTopResultModelAtOpponents(IReadOnlyList<OpponentSelectStatusModel> opponentModels);
    }
}
