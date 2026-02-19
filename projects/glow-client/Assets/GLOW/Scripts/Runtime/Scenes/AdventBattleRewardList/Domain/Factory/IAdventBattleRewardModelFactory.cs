using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Scenes.AdventBattleRewardList.Domain.Model;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Factory
{
    public interface IAdventBattleRewardModelFactory
    {
        IReadOnlyList<IAdventBattlePersonalRewardModel> CreatePersonalRankRewardModelFromRank(
            MasterDataId adventBattleId,
            MstAdventBattleScoreRankModel currentRankModel);

        IReadOnlyList<IAdventBattlePersonalRewardModel> CreatePersonalRankingRewardModels(
            MasterDataId adventBattleId);
        
        IReadOnlyList<AdventBattleRaidTotalScoreRewardModel> CreateRaidTotalScoreRewardModels(
            MasterDataId adventBattleId,
            AdventBattleRaidTotalScore raidTotalScore);
    }
}