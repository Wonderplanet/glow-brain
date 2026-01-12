using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record MessageTitle(ObscuredString Value)
    {
        public static MessageTitle Empty { get; } = new MessageTitle(string.Empty);
    }
}