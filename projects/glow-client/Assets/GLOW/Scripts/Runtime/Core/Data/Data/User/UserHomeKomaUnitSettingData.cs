using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.User
{
    [Serializable]
    public record UserHomeKomaUnitSettingData(
        MasterDataId MstUnitId,
        HomeMainKomaUnitAssetSetPlaceIndex PlaceIndex)
    {
        [JsonProperty("mst_unit_id")]
        public MasterDataId MstUnitId { get; } = MstUnitId;
        [JsonProperty("place_index")]
        public HomeMainKomaUnitAssetSetPlaceIndex PlaceIndex { get; } = PlaceIndex;
    };
}
