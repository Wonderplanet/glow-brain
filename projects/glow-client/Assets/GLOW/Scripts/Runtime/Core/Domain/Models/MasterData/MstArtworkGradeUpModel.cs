using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkGradeUpModel(
        MasterDataId Id,
        Rarity Rarity,
        ArtworkGradeLevel GradeLevel,
        MasterDataId MstSeriesId,
        MasterDataId MstArtworkId,
        IReadOnlyList<ArtworkGradeUpCostModel> GradeUpCostModels)
    {
        public static MstArtworkGradeUpModel Empty { get; } = new(
            MasterDataId.Empty,
            Rarity.R,
            ArtworkGradeLevel.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            new List<ArtworkGradeUpCostModel>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
