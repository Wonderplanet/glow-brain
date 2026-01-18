using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstPvpDataRepository
    {
        //MstPvpModel
        IReadOnlyList<MstPvpModel> GetMstPvpModels();
        MstPvpModel GetMstPvpModelFirstOrDefault(ContentSeasonSystemId sysPvpSeasonId);

        //MstPvpSeasonModel
        MstPvpBattleModel GetMstPvpBattleModelFirstOrDefault(ContentSeasonSystemId sysPvpSeasonId);
        IReadOnlyList<MstPvpRankModel> GetMstPvpRanks();
        MstPvpRankModel GetCurrentPvpRankModel(PvpPoint pvpPoint);
        MstPvpRankModel GetNextPvpRankModel(PvpPoint pvpPoint);

        //MstPvpRewardGroupModel
        IReadOnlyList<MstPvpRewardGroupModel> GetMstPvpRewardGroups(ContentSeasonSystemId sysPvpSeasonId);
    }
}
