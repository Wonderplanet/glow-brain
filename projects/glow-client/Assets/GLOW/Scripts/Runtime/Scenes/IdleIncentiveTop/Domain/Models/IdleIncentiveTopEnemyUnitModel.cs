using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Models
{
    public record IdleIncentiveTopEnemyUnitModel(
        UnitImageAssetPath UnitImageAssetPath,
        PhantomizedFlag IsPhantomized)
    {
        public static IdleIncentiveTopEnemyUnitModel Empty { get; } = new(
            UnitImageAssetPath.Empty,
            PhantomizedFlag.False);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}