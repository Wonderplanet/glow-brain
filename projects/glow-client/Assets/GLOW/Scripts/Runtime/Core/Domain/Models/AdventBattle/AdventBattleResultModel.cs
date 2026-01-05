using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleResultModel(
        MasterDataId MstAdventBattleId,
        AdventBattleMyRankingModel MyRanking,
        AdventBattleScore Score)
    {
        public static AdventBattleResultModel Empty { get; } = new(
            MasterDataId.Empty,
            AdventBattleMyRankingModel.Empty,
            AdventBattleScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}