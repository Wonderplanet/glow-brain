using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserPropertyModel(
        BgmMuteFlag IsBgmMute,
        SeMuteFlag IsSeMute,
        SpecialAttackCutInPlayType SpecialAttackCutInPlayType,
        PushOffFlag IsPushOff,
        TwoRowDeckModeFlag IsTwoRowDeck,
        DamageDisplayFlag IsDamageDisplay)
    {
        public static UserPropertyModel Empty { get; } = new (
            BgmMuteFlag.False,
            SeMuteFlag.False,
            SpecialAttackCutInPlayType.Off,
            PushOffFlag.False,
            TwoRowDeckModeFlag.True,
            DamageDisplayFlag.True);
    }
}
