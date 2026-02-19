using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpTop.Domain.Resolver
{
    public interface IMstCurrentPvpModelResolver
    {
        MstPvpModel CreateMstPvpModel(ContentSeasonSystemId sysPvpSeasonId);
        MstPvpBattleModel CreateMstPvpBattleModel(ContentSeasonSystemId sysPvpSeasonId);
        IReadOnlyList<MstPvpRewardGroupModel> CreateMstPvpRewardGroups(ContentSeasonSystemId sysPvpSeasonId);
    }
}