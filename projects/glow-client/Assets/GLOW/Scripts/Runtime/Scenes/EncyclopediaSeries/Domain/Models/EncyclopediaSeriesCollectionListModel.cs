using System.Collections.Generic;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.Models
{
    public record EncyclopediaSeriesCollectionListModel(IReadOnlyList<EncyclopediaArtworkListCellModel> ArtworkList, IReadOnlyList<EncyclopediaEmblemListCellModel> EmblemList);
}
