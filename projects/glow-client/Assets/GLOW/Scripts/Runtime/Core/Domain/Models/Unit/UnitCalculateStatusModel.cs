using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models.Unit
{
    public record UnitCalculateStatusModel(HP HP, AttackPower AttackPower)
    {
        public static UnitCalculateStatusModel Empty { get; } = new(HP.Empty, AttackPower.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
