using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.User
{
    [Serializable]
    public record UserHomeKomaSettingData(
        MasterDataId MstHomeKomaPatternId,
        IReadOnlyList<UserHomeKomaUnitSettingData> UserHomeKomaUnitSettingDatas
    )
    {
        [JsonProperty("mst_home_koma_pattern_id")]
        public MasterDataId MstHomeKomaPatternId { get; } = MstHomeKomaPatternId;
        [JsonProperty("user_home_koma_unit_settings")]
        public IReadOnlyList<UserHomeKomaUnitSettingData> UserHomeKomaUnitSettingDatas { get; } = UserHomeKomaUnitSettingDatas;
    }
}
