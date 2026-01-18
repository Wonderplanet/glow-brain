using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Mission
{
    public record MissionProgressGroupKey(ObscuredString Value)
    {
        public static MissionProgressGroupKey Empty { get; } = new(string.Empty);
    }
}