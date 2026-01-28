using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record DrawCountThresholdGroupId(ObscuredString Value)
    {
        public static DrawCountThresholdGroupId Empty { get; } = new DrawCountThresholdGroupId("");

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
