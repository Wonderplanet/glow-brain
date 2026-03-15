using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.Models
{
    public record IdleIncentiveTopPlayerUnitModel(
        UnitAssetKey AssetKey,
        UnitImageAssetPath UnitImageAssetPath,
        CharacterUnitRoleType RoleType,
        TickCount AttackDelay,
        AttackRange AttackRange)
    {
        public static IdleIncentiveTopPlayerUnitModel Empty { get; } = new(
            UnitAssetKey.Empty,
            UnitImageAssetPath.Empty,
            CharacterUnitRoleType.None,
            TickCount.Zero,
            AttackRange.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}