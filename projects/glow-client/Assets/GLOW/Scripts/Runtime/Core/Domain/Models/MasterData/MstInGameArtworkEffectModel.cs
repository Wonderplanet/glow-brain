using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstInGameArtworkEffectModel(
        MasterDataId MstArtworkId,
        IReadOnlyList<MstArtworkEffectModel> MstArtworkEffectModels)
    {
        public static MstInGameArtworkEffectModel Empty { get; } = new (
            MasterDataId.Empty,
            new List<MstArtworkEffectModel>());

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
