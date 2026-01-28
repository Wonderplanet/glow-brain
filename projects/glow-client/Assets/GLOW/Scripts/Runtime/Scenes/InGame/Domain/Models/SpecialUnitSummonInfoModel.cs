using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialUnitSummonInfoModel(
        CanSpecialUnitSummonFlag CanSpecialUnitSummonFlag,
        SpecialUnitSummonKomaRange KomaRange,
        SpecialUnitSummonPositionSelectingFlag IsSummonPositionSelecting)
    {
        public static SpecialUnitSummonInfoModel Empty { get; } = new SpecialUnitSummonInfoModel(
            CanSpecialUnitSummonFlag.False,
            SpecialUnitSummonKomaRange.Empty,
            SpecialUnitSummonPositionSelectingFlag.False
        );
    }
}
