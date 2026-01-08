using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.ValueObjects;

namespace GLOW.Scenes.HomeMenuSetting.Domain.Model
{
    public record HomeMenuSettingModel(
        BgmMuteFlag IsBgmMute, 
        SeMuteFlag IsSeMute, 
        DamageDisplayFlag IsDamageDisplay,
        SpecialAttackCutInPlayType SpecialAttackCutInPlayType, 
        PushOffFlag IsPushOff,
        ApplicationVersion AppVersion);
}