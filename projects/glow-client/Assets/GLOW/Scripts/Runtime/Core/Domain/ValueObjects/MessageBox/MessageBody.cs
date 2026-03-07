using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.MessageBox
{
    public record MessageBody(ObscuredString Value)
    {
        public static MessageBody Empty { get; } = new MessageBody(string.Empty);
    }
}