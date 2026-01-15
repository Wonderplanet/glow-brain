using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaDescription(ObscuredString Value)
    {
        public static GachaDescription Empty { get; } = new GachaDescription("");

        public override string ToString()
        {
            return Value;
        }
    }
}
