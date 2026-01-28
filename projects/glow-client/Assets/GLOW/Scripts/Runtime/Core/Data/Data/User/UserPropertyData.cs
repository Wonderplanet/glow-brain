using System;
using GLOW.Modules.GameOption.Domain.Constants;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.User
{
    [Serializable]
    public record UserPropertyData(
        bool IsBgmMute, 
        bool IsSeMute, 
        SpecialAttackCutInPlayType SpecialAttackCutInPlayType, 
        bool IsPushOff,
        bool IsTwoRowDeck,
        bool IsDamageDisplay)
    {
        [JsonProperty("bgm_mute")]
        public bool IsBgmMute { get; } = IsBgmMute;
        [JsonProperty("se_mute")]
        public bool IsSeMute { get; } = IsSeMute;
        [JsonProperty("special_attack_animation_playing")]
        public SpecialAttackCutInPlayType SpecialAttackCutInPlayType { get; } = SpecialAttackCutInPlayType;
        [JsonProperty("push_off")]
        public bool IsPushOff { get; } = IsPushOff;
        [JsonProperty("two_row_deck")]
        public bool IsTwoRowDeck { get; } = IsTwoRowDeck;
        [JsonProperty("damage_display")]
        public bool IsDamageDisplay { get; } = IsDamageDisplay;
    }
}
