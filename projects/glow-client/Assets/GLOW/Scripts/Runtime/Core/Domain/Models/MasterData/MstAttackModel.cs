using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstAttackModel(MasterDataId Id, AttackData AttackData)
    {
        public static MstAttackModel Empty { get; } = new(MasterDataId.Empty, AttackData.Empty);
        public bool IsEmpty => ReferenceEquals(this, Empty);
    };
}