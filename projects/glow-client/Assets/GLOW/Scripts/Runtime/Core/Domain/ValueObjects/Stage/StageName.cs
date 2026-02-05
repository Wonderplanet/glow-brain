using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageName(ObscuredString Value)
    {
        public static StageName Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public InGameName ToInGameName()
        {
            return new InGameName(Value);
        }
    }
}
