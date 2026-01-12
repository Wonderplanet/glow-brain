using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Scenes.PvpBattleResult.Domain.Model
{
    public record PvpBattleResultPointModel(
        PvpRankClassType CurrentRankType,
        PvpRankLevel CurrentRankLevel,
        IReadOnlyList<PvpBattleResultPointRankTargetModel> PvpResultPointRankTargetModels,
        PvpPoint VictoryPoint,
        PvpPoint OpponentBonusPoint,
        PvpPoint TimeBonusPoint,
        PvpPoint TotalPoint)
    {
        public static PvpBattleResultPointModel Empty { get; } = new PvpBattleResultPointModel(
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            new List<PvpBattleResultPointRankTargetModel>(),
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}