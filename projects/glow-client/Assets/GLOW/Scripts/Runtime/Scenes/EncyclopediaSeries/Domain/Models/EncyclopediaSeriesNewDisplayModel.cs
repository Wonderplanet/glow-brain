using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.Models
{
    public record EncyclopediaSeriesNewDisplayModel(
        IReadOnlyList<MasterDataId> NewDisplayPlayerUnitIds,
        IReadOnlyList<MasterDataId> NewDisplayEnemyUnitIds,
        IReadOnlyList<MasterDataId> NewDisplayArtworkIds,
        IReadOnlyList<MasterDataId> NewDisplayEmblemIds
    );
}
