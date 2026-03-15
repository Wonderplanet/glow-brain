using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Domain.Models
{
    public record MstOutpostEnhancementModel(
        MasterDataId Id,
        MasterDataId OutpostId,
        OutpostEnhancementType Type,
        OutpostEnhanceIconAssetKey IconAssetKey,
        OutpostEnhanceName Name,
        IReadOnlyList<MstOutpostEnhancementLevelModel> Levels)
    {
        public static MstOutpostEnhancementModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            OutpostEnhancementType.LeaderPointSpeed,
            OutpostEnhanceIconAssetKey.Empty,
            OutpostEnhanceName.Empty,
            new List<MstOutpostEnhancementLevelModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
