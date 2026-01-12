using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.ViewModels
{
    public record UnitSpecialAttackPreviewViewModel(
        CharacterColor UnitColor,
        UnitImageAssetPath UnitImageAssetPath,
        UnitAssetKey UnitAssetKey,
        TickCount ChargeTime,
        TickCount ActionDuration,
        IsEncyclopediaSpecialAttackPositionRight IsRight);
}
