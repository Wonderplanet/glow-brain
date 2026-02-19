using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Domain.Models.AdventBattle
{
    public record AdventBattleRankingResultModel(
        IReadOnlyList<AdventBattleRankingItemModel> RankingList,
        AdventBattleMyRankingModel MyRanking)
    {
        public static AdventBattleRankingResultModel Empty { get; } = new(
            new List<AdventBattleRankingItemModel>(),
            AdventBattleMyRankingModel.Empty);
    }
}