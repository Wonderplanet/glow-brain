using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaUnlockDurationHours(ObscuredInt Value)
    {
        public static GachaUnlockDurationHours Empty { get; } = new(-1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}