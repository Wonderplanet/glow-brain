using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventName(ObscuredString Value)
    {
        public static EventName Empty { get; } = new EventName(string.Empty);
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
