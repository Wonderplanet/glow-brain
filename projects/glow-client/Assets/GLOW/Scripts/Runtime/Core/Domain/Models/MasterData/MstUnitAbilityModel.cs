using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitAbilityModel(MasterDataId Id, UnitAbility UnitAbility)
    {
        public static MstUnitAbilityModel Empty { get; } = new(MasterDataId.Empty, UnitAbility.Empty);
        public bool IsEmpty => ReferenceEquals(this, Empty);
    };
}
