using System.Collections.Generic;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstArtworkEffectDescriptionModel(
        MasterDataId Id,
        MasterDataId MstArtworkId,
        IReadOnlyList<ArtworkEffectDescriptionModel> Descriptions)
    {
        public static MstArtworkEffectDescriptionModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            new List<ArtworkEffectDescriptionModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
