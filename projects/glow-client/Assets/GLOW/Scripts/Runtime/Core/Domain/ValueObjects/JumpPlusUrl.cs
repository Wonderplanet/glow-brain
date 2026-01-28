using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record JumpPlusUrl(ObscuredString Value)
    {
        public static JumpPlusUrl Empty { get; } = new JumpPlusUrl(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
