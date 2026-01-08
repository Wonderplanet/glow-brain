using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstPointModel(
        MasterDataId Id,
        PointName Name,
        Rarity Rarity,
        SortOrder SortOrder,
        PointAssetKey AssetKey);
}
