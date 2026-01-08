using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventBonusGroupId(ObscuredString Value)
    {
        public static EventBonusGroupId Empty { get; } = new EventBonusGroupId(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
