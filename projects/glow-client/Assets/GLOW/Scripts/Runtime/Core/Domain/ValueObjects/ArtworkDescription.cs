using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkDescription(ObscuredString Value)
    {
        public static ArtworkDescription Empty { get; } = new(string.Empty);

        public PlayerResourceDescription ToPlayerResourceDescription()
        {
            return new PlayerResourceDescription(Value);
        }
    };
}
