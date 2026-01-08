using System;
using System.Collections.Generic;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.User
{
    [Serializable]
    public class SpecialAttackCutInLogData
    {
        [JsonProperty("special_attack_once_a_day_option_date")]
        public string SpecialAttackOnceADayDate { get; set; }
        [JsonProperty("played_special_attack_unit_ids")]
        public List<string> PlayedSpecialAttackUnitIds { get; set; }
    }
}