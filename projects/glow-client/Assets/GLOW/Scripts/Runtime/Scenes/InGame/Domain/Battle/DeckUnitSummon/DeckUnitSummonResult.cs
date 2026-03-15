using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public record DeckUnitSummonResult(DeckUnitModel UpdatedDeckUnit, BattlePointModel UpdatedBattlePointModel)
    {
        public static DeckUnitSummonResult Empty { get; } = new DeckUnitSummonResult(
            DeckUnitModel.Empty,
            BattlePointModel.Empty);
    }
}
