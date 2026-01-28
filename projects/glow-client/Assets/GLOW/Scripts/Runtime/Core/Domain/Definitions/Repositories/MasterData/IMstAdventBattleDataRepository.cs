using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstAdventBattleDataRepository
    {
        IReadOnlyList<MstAdventBattleModel> GetMstAdventBattleModels();
        MstAdventBattleModel GetMstAdventBattleModel(MasterDataId mstAdventBattleId);
        MstAdventBattleModel GetMstAdventBattleModelFirstOrDefault(MasterDataId mstAdventBattleId);
        IReadOnlyList<MstAdventBattleRewardGroupModel> GetMstAdventBattleRewardGroups(MasterDataId mstAdventBattleId);
        IReadOnlyList<MstAdventBattleScoreRankModel> GetMstAdventBattleScoreRanks(MasterDataId mstAdventBattleId);
        MstAdventBattleScoreRankModel GetMstAdventBattleScoreRank(MasterDataId mstAdventBattleScoreRankId);
        IReadOnlyList<MstAdventBattleClearRewardModel> GetMstAdventBattleClearRewardModels(MasterDataId mstAdventBattleId);

    }
}
