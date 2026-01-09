using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record SpecialUnitSummonKomaRange(
        KomaCount Range,
        KomaCount MaxKomaCount)
    {
        public static SpecialUnitSummonKomaRange Empty { get; } = new(
            KomaCount.Empty,
            KomaCount.Empty);

        public bool IsInRange(KomaNo komaNo, BattleSide battleSide)
        {
            return IsInRange(komaNo.Value, battleSide);
        }

        public bool IsInRange(int index, BattleSide battleSide)
        {
            return battleSide == BattleSide.Player
                ? Range > index
                : (MaxKomaCount - Range) <= index;
        }
    }
}
