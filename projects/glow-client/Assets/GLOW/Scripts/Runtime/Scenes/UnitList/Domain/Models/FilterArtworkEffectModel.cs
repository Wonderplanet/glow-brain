using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.UnitList.Domain.Models
{
    public record FilterArtworkEffectModel(IReadOnlyList<ArtworkEffectType> ArtworkEffectTypes)
    {
        public static FilterArtworkEffectModel Default { get; } = new FilterArtworkEffectModel(new List<ArtworkEffectType>());

        public bool IsAnyFilter => ArtworkEffectTypes.Count > 0;

        public bool IsOn(ArtworkEffectType artworkEffectType)
        {
            return ArtworkEffectTypes.Contains(artworkEffectType);
        }
    }
}
