using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record ColorCodeTheme(ObscuredString Value)
    {
        public static ColorCodeTheme TextRed { get; } = new ColorCodeTheme("#ee3632");

        public override string ToString()
        {
            return Value;
        }
    }
}
