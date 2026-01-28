using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameMenuModel(
        BgmMuteFlag IsBgmMute,
        SeMuteFlag IsSeMute,
        SpecialAttackCutInPlayType SpecialAttackCutInPlayType,
        TwoRowDeckModeFlag IsTwoRowDeck,
        DamageDisplayFlag IsDamageDisplay,
        InGameConsumptionType InGameConsumptionType,
        CanGiveUpFlag CanGiveUp,
        InGameTypePvpFlag IsInGameTypePvp);
}
