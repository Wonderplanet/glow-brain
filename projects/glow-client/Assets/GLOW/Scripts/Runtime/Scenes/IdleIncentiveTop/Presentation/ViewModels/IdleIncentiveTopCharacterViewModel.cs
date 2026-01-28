using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels
{
    public record IdleIncentiveTopCharacterViewModel(
        UnitImageAssetPath PlayerUnitImageAssetPath,
        UnitImageAssetPath EnemyUnitImageAssetPath,
        PhantomizedFlag EnemyIsPhantomized,
        CharacterUnitRoleType PlayerUnitRoleType,
        TickCount PlayerCharacterAttackDelay,
        AttackRange PlayerCharacterAttackRange,
        UnitAssetKey PlayerCharacterAssetKey,
        KomaBackgroundAssetKey BackgroundAssetKey);
}
