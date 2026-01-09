using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record DropBattlePoint(ObscuredInt Value)
    {
        public bool IsEmpty() => ReferenceEquals(this, Empty);
        public static DropBattlePoint Empty { get; } = new (0);
    };
}
