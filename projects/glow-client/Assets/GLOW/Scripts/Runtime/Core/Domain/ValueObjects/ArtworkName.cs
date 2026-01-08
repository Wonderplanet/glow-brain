using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ArtworkName(ObscuredString Value)
    {
        public static ArtworkName Empty { get; } = new(string.Empty);

        public PlayerResourceName ToPlayerResourceName()
        {
            return new PlayerResourceName(Value);
        }
    };
}
