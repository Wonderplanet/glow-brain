using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaName(ObscuredString Value)
    {
        public static GachaName Empty { get; } = new GachaName(string.Empty);

        public bool IsEmpty()
        {
            return Value == Empty.Value;
        }
    }
}
