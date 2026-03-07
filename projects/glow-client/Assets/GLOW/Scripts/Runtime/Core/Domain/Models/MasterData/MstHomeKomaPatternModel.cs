using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstHomeKomaPatternModel(
        MasterDataId Id,
        HomeMainKomaPatternAssetKey AssetKey,
        HomeMainKomaPatternName Name);
}