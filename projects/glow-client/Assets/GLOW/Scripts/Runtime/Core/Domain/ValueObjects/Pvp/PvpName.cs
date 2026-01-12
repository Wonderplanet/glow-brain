using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpName(ObscuredString Value)
    {
        public static PvpName Empty { get; } = new PvpName(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public EventName ToEventName()
        {
            return new EventName(Value);
        }
    }
}
