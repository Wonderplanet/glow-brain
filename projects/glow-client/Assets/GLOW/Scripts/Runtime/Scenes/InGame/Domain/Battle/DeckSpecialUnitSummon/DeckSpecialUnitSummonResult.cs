using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public record DeckSpecialUnitSummonResult(DeckUnitModel UpdatedDeckUnit, BattlePointModel UpdatedBattlePointModel)
    {
        public static DeckSpecialUnitSummonResult Empty { get; } = new DeckSpecialUnitSummonResult(
            DeckUnitModel.Empty,
            BattlePointModel.Empty);
    }
}
